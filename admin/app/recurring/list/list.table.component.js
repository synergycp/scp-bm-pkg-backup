(function () {
    'use strict';

    angular
        .module('pkg.backup.recurring.list')
        .component('pkgBackupRecurringTable', {
            require: {
                list: '\^list',
            },
            bindings: {
                showSource: '=?',
                showDestination: '=?',
                showPeriod: '=?',
                showCreatedAt: '=?',
                showUpdatedAt: '=?',
            },
            controller: 'RecurringTableCtrl as table',
            transclude: true,
            templateUrl: tableTemplateUrl
        })
        .controller('RecurringTableCtrl', RecurringTableCtrl)
    ;

    /**
     * @ngInject
     */
    function tableTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/recurring/list/list.table.html')
            ;
    }

    /**
     * @ngInject
     */
    function RecurringTableCtrl() {
        var table = this;

        table.$onInit = init;

        ///////////

        function init() {
            _.defaults(table, {
                showSource: true,
                showDestination: true,
                showPeriod: true,
                showCreatedAt: true,
                showUpdatedAt: true,
            });
        }
    }
})();
