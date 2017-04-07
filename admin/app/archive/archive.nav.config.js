(function () {
    'use strict';

    angular
        .module('pkg.backup.archive')
        .constant('PkgBackupArchiveNav', {
            text: "Backup Archives",
            sref: "app.pkg.backup.archive.list",
            alertType: 'danger',
            refreshInterval: 10000,
        })
        .config(NavConfig)
        .run(NavRun)
    ;

    /**
     * @ngInject
     */
    function NavConfig(NavProvider, PkgBackupArchiveNav) {
        NavProvider
            .group('system')
            .item(PkgBackupArchiveNav)
        ;
    }

    /**
     * @ngInject
     */
    function NavRun(PkgBackupArchiveNav, $interval, RouteHelpers, Auth) {
        var $archives = RouteHelpers
            .package('backup')
            .api()
            .all('archive');

        var interval;

        Auth.whileLoggedIn(startChecking, stopChecking);

        ///////////

        function startChecking() {
            stopChecking();
            load();

            interval = $interval(load, PkgBackupArchiveNav.refreshInterval);
        }

        function stopChecking() {
            if (interval) {
                $interval.cancel(interval);
            }
        }

        function load() {
            return $archives
                .getList({
                    per_page: 1,
                    pending_client: true,
                })
                .then(function (items) {
                    PkgBackupArchiveNav.alert = items.meta.total;
                    PkgBackupArchiveNav.group.syncAlerts();
                });
        }
    }
})();
