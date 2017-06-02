(function () {
    angular
        .module('pkg.backup.destination')
        .config(routeConfig)
    ;

    /**
     * @ngInject
     */
    function routeConfig(RouteHelpersProvider) {
        var helper = RouteHelpersProvider;
        var pkg = helper.package('backup');
        pkg
            .state('destination', {
                url: '/destination',
                abstract: true,
                template: helper.dummyTemplate,
                resolve: helper.resolveFor(pkg.lang('admin:destination')),
            })
            .state('destination.view', {
                url: '/:id',
                title: 'View Destination Backups',
                controller: 'DestinationViewCtrl as vm',
                templateUrl: pkg.asset('admin/destination/destination.view.html'),
            })
            .url('destination/?([0-9]*)', mapRecurrentUrl)
            .sso('destination', function($state, options) {
                return mapRecurrentUrl($state, options.id);
            })
        ;

        function mapRecurrentUrl($state, id) {
            return $state.href('destination.' + (id ? 'view' : 'list'), {
                id: id
            });
        }
    }
})();
