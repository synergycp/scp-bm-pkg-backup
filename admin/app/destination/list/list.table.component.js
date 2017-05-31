(function () {
    'use strict';

    angular
        .module('pkg.backup.destination.list')
        .component('pkgBackupDestinationTable', {
            require: {
                list: '\^list',
            },
            bindings: {
                showName: '=?',
                showHandler: '=?',
            },
            controller: 'DestinationTableCtrl as table',
            transclude: true,
            templateUrl: tableTemplateUrl
        })
        .controller('DestinationTableCtrl', DestinationTableCtrl)
    ;

    /**
     * @ngInject
     */
    function tableTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/destination/list/list.table.html')
            ;
    }

    /**
     * @ngInject
     */
    function DestinationTableCtrl() {
        var table = this;

        table.$onInit = init;

        ///////////

        function init() {
            _.defaults(table, {
                showName: true,
                showHandler: true,
            });
        }
    }
})();
