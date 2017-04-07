(function () {
    angular
        .module('pkg.backup.archive')
        .config(routeConfig)
    ;

    /**
     * @ngInject
     */
    function routeConfig(RouteHelpersProvider) {
        var helper = RouteHelpersProvider;
        var pkg = helper.package('backup');
        pkg
            .state('archive', {
                url: '/archive',
                abstract: true,
                template: helper.dummyTemplate,
                resolve: helper.resolveFor(pkg.lang('admin:archive')),
            })
            .state('archive.view', {
                url: '/:id',
                title: 'View Report',
                controller: 'ReportViewCtrl as vm',
                templateUrl: pkg.asset('admin/archive/archive.view.html'),
            })
            .url('archive/?([0-9]*)', mapReportUrl)
            .sso('archive', function($state, options) {
                return mapReportUrl($state, options.id);
            })
        ;

        function mapReportUrl($state, id) {
            return $state.href('archive.' + (id ? 'view' : 'list'), {
                id: id,
            });
        }
    }
})();
