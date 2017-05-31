(function () {
    'use strict';

    angular
        .module('pkg.backup.destination', [
            'scp.angle.layout.list',
            'scp.core.api',
            'pkg.backup.destination.list',
        ]);
})();
