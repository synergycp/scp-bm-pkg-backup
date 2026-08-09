#!/usr/bin/env bash
# Sandboxed smoke tests for dev/restore.sh (the customer-facing
# backup-restore.sh). Stubs root detection, docker, wget and the
# scp-db/scp-exec binaries via a restricted PATH and path rewriting, then
# exercises the happy paths (fresh install, resume, encrypted backups) and
# every guarded failure path. Safe to run anywhere: nothing outside a temp
# sandbox is touched.
#
# Usage: bash dev/restore-tests.sh
set -u

SRC=$(cd "$(dirname "$0")" && pwd)/restore.sh
SANDBOX_APP_KEY='base64:sandbox-key'
SANDBOX=$(mktemp -d "${TMPDIR:-/tmp}/restore-tests-XXXXXX")
trap 'rm -rf "$SANDBOX"' EXIT
PASS=0; FAIL=0

check() { # label, condition-result
  if [ "$2" -eq 0 ]; then PASS=$((PASS+1)); echo "PASS $1";
  else FAIL=$((FAIL+1)); echo "FAIL $1"; fi
}

reset-sandbox() { # $1 = docker state: none | installed | running
  rm -rf "$SANDBOX"
  mkdir -p "$SANDBOX/stubs" "$SANDBOX/binlinks" "$SANDBOX/work" "$SANDBOX/scp/bin" "$SANDBOX/tmp" "$SANDBOX/log"

  # Restricted PATH: only these real tools exist, so the machine's real
  # docker/wget can never leak into the test.
  for tool in bash sh tee date gunzip gzip tar head cat rm mkdir cp chmod touch openssl mktemp sed; do
    ln -s "$(command -v $tool)" "$SANDBOX/binlinks/$tool"
  done

  # Test copy of the script with absolute paths rewritten into the sandbox.
  sed -e "s|/scp/bin/scp-exec|$SANDBOX/scp/bin/scp-exec|" \
      -e "s|^SCP_ROOT_DIR=/scp$|SCP_ROOT_DIR=$SANDBOX/scp|" \
      -e "s|cd /tmp |cd $SANDBOX/tmp |" \
      "$SRC" > "$SANDBOX/backup-restore.sh"

  # whoami -> root so the sudo re-exec is skipped.
  printf '#!/bin/sh\necho root\n' > "$SANDBOX/stubs/whoami"

  # docker stub: 'ps' prints the container name when the marker exists;
  # exec/cp are recorded and succeed. Written to docker.stub; scenarios (and
  # the fake installer) place it into stubs/ as 'docker'.
  cat > "$SANDBOX/docker.stub" <<STUB
#!/bin/sh
echo "docker \$@" >> "$SANDBOX/log/docker.log"
case "\$1" in
  ps) [ -f "$SANDBOX/container-running" ] && echo scp-bm-app_php_server_1 ;;
  exec) if [ "\$2" = "-i" ]; then cat >> "$SANDBOX/log/container-script.log"; fi ;;
  cp) : ;;
esac
exit 0
STUB

  # wget stub: records the call and writes a fake installer that "installs
  # docker" (drops the stub into PATH) and starts the "container".
  cat > "$SANDBOX/stubs/wget" <<STUB
#!/bin/sh
echo "wget \$@" >> "$SANDBOX/log/wget.log"
cat > "app.sh" <<'INNER'
cp "__SANDBOX__/docker.stub" "__SANDBOX__/stubs/docker"
chmod +x "__SANDBOX__/stubs/docker"
touch "__SANDBOX__/container-running"
INNER
exit 0
STUB
  sed -i "s|__SANDBOX__|$SANDBOX|g" "$SANDBOX/stubs/wget"

  # scp-exec / scp-db: record stdin and succeed (scp-db can be told to fail).
  printf '#!/bin/sh\ncat >> "%s/log/scp-exec.log"\nexit 0\n' "$SANDBOX" > "$SANDBOX/scp/bin/scp-exec"
  cat > "$SANDBOX/scp/bin/scp-db" <<STUB
#!/bin/sh
cat >> "$SANDBOX/log/scp-db.log"
[ -f "$SANDBOX/scp-db-fails" ] && exit 1
exit 0
STUB

  chmod +x "$SANDBOX/stubs/"* "$SANDBOX/scp/bin/"* "$SANDBOX/docker.stub"

  case "$1" in
    none)
      # docker itself is absent on a fresh server until the installer runs.
      rm -f "$SANDBOX/container-running"
      ;;
    installed)
      cp "$SANDBOX/docker.stub" "$SANDBOX/stubs/docker"
      rm -f "$SANDBOX/container-running"
      ;;
    running)
      cp "$SANDBOX/docker.stub" "$SANDBOX/stubs/docker"
      touch "$SANDBOX/container-running"
      ;;
  esac

  # Valid backup files.
  cd "$SANDBOX/work"
  echo 'CREATE TABLE t (id INT);' | gzip > database.gz
  mkdir -p cfg && (cd cfg && echo "APP_KEY=$SANDBOX_APP_KEY" > .env && echo priv > id_rsa && echo pub > id_rsa.pub \
    && tar -czf ../synergycp-config-backup.tar.gz .env id_rsa id_rsa.pub)
  rm -rf cfg
}

make-encrypted-db() { # $1 = passphrase
  cd "$SANDBOX/work"
  rm -f database.gz
  echo 'CREATE TABLE enc_t (id INT);' | gzip |
    openssl enc -aes-256-cbc -pbkdf2 -iter 100000 -md sha256 -salt -pass "pass:$1" \
    > database.gz.enc
}

run-script() {
  cd "$SANDBOX/work" && \
  env PATH="$SANDBOX/stubs:$SANDBOX/binlinks" bash "$SANDBOX/backup-restore.sh" "$@" \
    > "$SANDBOX/log/stdout.log" 2>&1
}

# ---- Test 1: happy path on a fresh server -------------------------------
reset-sandbox none
run-script -y
RC=$?
check "fresh server restore exits 0" $RC
grep -q 'wget -qO app.sh' "$SANDBOX/log/wget.log";               check "installer downloaded with -O (no stale app.sh)" $?
grep -q 'Install the latest SynergyCP' "$SANDBOX/log/stdout.log"; check "plan shown before work" $?
grep -q 'cd "/var/www/html"' "$SANDBOX/log/container-script.log"; check "container script cds to app dir explicitly" $?
grep -q 'set -e' "$SANDBOX/log/container-script.log";             check "container script uses set -e" $?
grep -q 'rm -rf /tmp/scp-backup' "$SANDBOX/log/docker.log";       check "temp config (secret key) removed from container" $?
grep -q 'DROP TABLE IF EXISTS' "$SANDBOX/log/scp-db.log";         check "database cleared" $?
grep -q 'CREATE TABLE t' "$SANDBOX/log/scp-db.log";               check "database imported" $?
grep -q 'version:update:complete' "$SANDBOX/log/scp-exec.log";    check "version update ran" $?
grep -q 'Remaining manual steps' "$SANDBOX/log/stdout.log";       check "manual follow-up checklist printed" $?
grep -q 'Restore started at' "$SANDBOX/work/restore.log";         check "restore.log written" $?

# ---- Test 2: truncated database.gz fails before any destructive step ----
reset-sandbox none
cd "$SANDBOX/work" && head -c 10 database.gz > tmp && mv tmp database.gz
run-script -y
[ $? -ne 0 ]; check "corrupt database.gz aborts" $?
grep -q 'not a valid gzip file' "$SANDBOX/log/stdout.log";        check "corrupt gz message shown" $?
[ ! -f "$SANDBOX/log/wget.log" ];                                 check "aborted before installing anything" $?

# ---- Test 3: config tarball missing id_rsa fails up front ----------------
reset-sandbox none
cd "$SANDBOX/work" && mkdir -p cfg && (cd cfg && echo APP_KEY=x > .env && echo pub > id_rsa.pub \
  && tar -czf ../synergycp-config-backup.tar.gz .env id_rsa.pub)
run-script -y
[ $? -ne 0 ]; check "config backup missing id_rsa aborts" $?
grep -q 'does not contain id_rsa' "$SANDBOX/log/stdout.log";      check "missing member named in error" $?

# ---- Test 4: docker installed but no SCP container -> clear error --------
reset-sandbox installed
run-script -y
[ $? -ne 0 ]; check "partial install (docker, no container) aborts" $?
grep -q 'no SynergyCP container' "$SANDBOX/log/stdout.log";       check "ambiguous state explained" $?

# ---- Test 5: resume on an existing install skips the installer -----------
reset-sandbox running
run-script -y
RC=$?
check "resume run exits 0" $RC
[ ! -f "$SANDBOX/log/wget.log" ];                                 check "resume run skips installer download" $?
grep -q 'skipping the install step' "$SANDBOX/log/stdout.log";    check "resume message shown" $?

# ---- Test 6: failed import propagates (pipefail sanity) -------------------
reset-sandbox running
touch "$SANDBOX/scp-db-fails"
run-script -y
[ $? -ne 0 ]; check "scp-db failure aborts the restore" $?
grep -q 'Failed to clear database' "$SANDBOX/log/stdout.log";     check "failure surfaced with step name" $?

# ---- Test 7: encrypted backup restores end to end -------------------------
reset-sandbox none
make-encrypted-db "$SANDBOX_APP_KEY"
run-script -y
RC=$?
check "encrypted restore exits 0" $RC
grep -q 'decrypted with the key' "$SANDBOX/log/stdout.log";       check "plan mentions decryption" $?
grep -q 'CREATE TABLE enc_t' "$SANDBOX/log/scp-db.log";           check "decrypted SQL reached the database" $?

# ---- Test 8: wrong key fails before any destructive step ------------------
reset-sandbox none
make-encrypted-db 'base64:some-other-panels-key'
run-script -y
[ $? -ne 0 ]; check "wrong-key encrypted backup aborts" $?
grep -q 'could not be decrypted' "$SANDBOX/log/stdout.log";       check "wrong-key message shown" $?
[ ! -f "$SANDBOX/log/wget.log" ];                                 check "wrong key aborts before install" $?

echo ""
echo "$PASS passed, $FAIL failed"
[ "$FAIL" -eq 0 ]
