(function () {
    angular
        .module('pkg.backup.recurring')
        .config(routeConfig)
    ;

    /**
     * @ngInject
     */
    function routeConfig(RouteHelpersProvider) {
        var helper = RouteHelpersProvider;
        var pkg = helper.package('backup');
        pkg
            .state('recurring', {
                url: '/recurring',
                abstract: true,
                template: helper.dummyTemplate,
                resolve: helper.resolveFor(pkg.lang('admin:recurring')),
            })
            .state('recurring.view', {
                url: '/:id',
                title: 'View Recurring Backups',
                controller: 'RecurringViewCtrl as vm',
                templateUrl: pkg.asset('admin/recurring/recurring.view.html'),
            })
            .url('recurring/?([0-9]*)', mapRecurrentUrl)
            .sso('recurring', function($state, options) {
                return mapRecurrentUrl($state, options.id);
            })
        ;

        function mapRecurrentUrl($state, id) {
            return $state.href('recurring.' + (id ? 'view' : 'list'), {
                id: id,
            });
        }
    }
})();
