(function () {
    angular
        .module('pkg.backup.archive.list')
        .config(routeConfig)
    ;

    /**
     * @ngInject
     */
    function routeConfig(RouteHelpersProvider) {
        var helper = RouteHelpersProvider;
        var pkg = helper.package('backup');
        pkg
            .state('archive.list', {
                url: '?search',
                title: 'Archives',
                controller: 'PkgBackupArchiveIndexCtrl as vm',
                templateUrl: pkg.asset('admin/archive/list/list.index.html'),
                reloadOnSearch: false,
            })
        ;
    }

})();
