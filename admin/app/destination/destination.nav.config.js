(function () {
    'use strict';

    angular
        .module('pkg.backup.destination')
        .constant('PkgBackupDestinationNav', {
            text: "Backup Destinations",
            sref: "app.pkg.backup.destination.list",
            alertType: 'danger',
            refreshInterval: 10000,
        })
        .config(NavConfig)
    ;

    /**
     * @ngInject
     */
    function NavConfig(NavProvider, PkgBackupDestinationNav) {
        NavProvider
            .group('system')
            .item(PkgBackupDestinationNav)
        ;
    }

})();
