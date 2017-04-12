(function () {
    'use strict';

    angular
        .module('pkg.backup.recurring', [
            'scp.angle.layout.list',
            'scp.core.api',
            'pkg.backup.recurring.list',
        ]);
})();
