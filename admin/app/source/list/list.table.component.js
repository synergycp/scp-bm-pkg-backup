(function () {
    'use strict';

    angular
        .module('pkg.backup.source.list')
        .component('pkgBackupSourceTable', {
            require: {
                list: '\^list',
            },
            bindings: {
                showName: '=?',
                showHandler: '=?',
            },
            controller: 'SourceTableCtrl as table',
            transclude: true,
            templateUrl: tableTemplateUrl
        })
        .controller('SourceTableCtrl', SourceTableCtrl)
    ;

    /**
     * @ngInject
     */
    function tableTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/source/list/list.table.html')
            ;
    }

    /**
     * @ngInject
     */
    function SourceTableCtrl() {
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
