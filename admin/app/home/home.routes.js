(function () {
    angular
        .module('pkg.backup.home')
        .config(routeConfig)
    ;

    /**
     * @ngInject
     */
    function routeConfig(RouteHelpersProvider) {
        var helper = RouteHelpersProvider;
        var pkg = helper.package('backup');
        pkg
            .state('home', {
                url: '',
                title: 'Backups',
                controller: 'PkgBackupHomeCtrl as vm',
                templateUrl: pkg.asset('admin/home/home.html'),
                resolve: helper.resolveFor(pkg.lang('admin:home'), pkg.lang('admin:widget')),
            })
        ;
    }
})();
