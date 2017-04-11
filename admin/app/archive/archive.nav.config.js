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

})();
