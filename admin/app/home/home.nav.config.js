(function () {
    'use strict';

    angular
        .module('pkg.backup.home')
        .constant('PkgBackupHomeNav', {
            text: "Backups",
            sref: "app.pkg.backup.home",
        })
        .config(NavConfig)
    ;

    /**
     * @ngInject
     */
    function NavConfig(NavProvider, PkgBackupHomeNav) {
        NavProvider
            .group('system')
            .item(PkgBackupHomeNav)
        ;
    }

})();
