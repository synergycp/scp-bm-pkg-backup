(function () {
    'use strict';

    angular
        .module('pkg.backup.source.list.filters')
        .component('sourceFilters', {
            require: {
                list: '\^list',
            },
            bindings: {
                show: '<',
                current: '=',
                change: '&?',
            },
            controller: 'SourceFiltersCtrl as filters',
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
            .trustedAsset('admin/source/list/filters/filters.html')
            ;
    }
})();
