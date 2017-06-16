(function () {
    'use strict';

    angular
        .module('pkg.backup.source')
        .constant('PkgBackupSourceNav', {
            text: "Backup Sources",
            sref: "app.pkg.backup.source.list",
            alertType: 'danger',
            refreshInterval: 10000,
        })
        .config(NavConfig)
    ;

    /**
     * @ngInject
     */
    function NavConfig(NavProvider, PkgBackupSourceNav) {
        NavProvider
            .group('system')
            .item(PkgBackupSourceNav)
        ;
    }

})();
