(function () {
    'use strict';

    angular
        .module('pkg.backup.home', [
            'scp.angle.layout.list',
            'scp.core.api',
            'pkg.backup.recurring',
            'pkg.backup.destination',
            'pkg.backup.archive',
        ]);
})();
