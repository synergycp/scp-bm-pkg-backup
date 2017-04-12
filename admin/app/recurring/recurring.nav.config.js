(function () {
    'use strict';

    angular
        .module('pkg.backup.recurring')
        .constant('PkgBackupRecurringNav', {
            text: "Recurring Backups",
            sref: "app.pkg.backup.recurring.list",
            alertType: 'danger',
            refreshInterval: 10000,
        })
        .config(NavConfig)
    ;

    /**
     * @ngInject
     */
    function NavConfig(NavProvider, PkgBackupRecurringNav) {
        NavProvider
            .group('system')
            .item(PkgBackupRecurringNav)
        ;
    }

})();
