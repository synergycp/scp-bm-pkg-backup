(function () {
  'use strict';

  angular
    .module('pkg.backup', [
        'pkg.backup.archive',
        'pkg.backup.destination',
        'pkg.backup.home',
        'pkg.backup.recurring',
    ]);
})();
