(function () {
  'use strict';

  angular
    .module('pkg.backup', [
        'pkg.backup.archive',
        'pkg.backup.recurring',
    ]);
})();
