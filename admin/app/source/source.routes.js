(function () {
    angular
        .module('pkg.backup.source')
        .config(routeConfig)
    ;

    /**
     * @ngInject
     */
    function routeConfig(RouteHelpersProvider) {
        var helper = RouteHelpersProvider;
        var pkg = helper.package('backup');
        pkg
            .state('source', {
                url: '/source',
                abstract: true,
                template: helper.dummyTemplate,
                resolve: helper.resolveFor(pkg.lang('admin:source')),
            })
            .state('source.view', {
                url: '/:id',
                title: 'View Source Backups',
                controller: 'SourceViewCtrl as vm',
                templateUrl: pkg.asset('admin/source/source.view.html'),
            })
            .url('source/?([0-9]*)', mapRecurrentUrl)
            .sso('source', function($state, options) {
                return mapRecurrentUrl($state, options.id);
            })
        ;

        function mapRecurrentUrl($state, id) {
            return $state.href('source.' + (id ? 'view' : 'list'), {
                id: id
            });
        }
    }
})();
