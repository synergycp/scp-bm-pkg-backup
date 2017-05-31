(function () {
    'use strict';

    angular
        .module('pkg.backup.destination.list.filters')
        .component('destinationFilters', {
            require: {
                list: '\^list',
            },
            bindings: {
                show: '<',
                current: '=',
                change: '&?',
            },
            controller: 'DestinationFiltersCtrl as filters',
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
            .trustedAsset('admin/destination/list/filters/filters.html')
            ;
    }
})();
