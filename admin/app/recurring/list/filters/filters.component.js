(function () {
    'use strict';

    angular
        .module('pkg.backup.recurring.list.filters')
        .component('recurringFilters', {
            require: {
                list: '\^list',
            },
            bindings: {
                show: '<',
                current: '=',
                change: '&?',
            },
            controller: 'RecurringFiltersCtrl as filters',
            transclude: true,
            templateUrl: filterTemplateUrl
        })
    ;

    /**
     * @ngInject
     */
    function filterTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/recurring/list/filters/filters.html')
            ;
    }
})();
