(function () {
    'use strict';

    angular
        .module('pkg.backup.source', [
            'scp.angle.layout.list',
            'scp.core.api',
            'pkg.backup.source.list',
        ]);
})();
