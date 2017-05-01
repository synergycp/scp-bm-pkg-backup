(function () {
    angular
        .module('pkg.backup.recurring.list')
        .config(routeConfig)
    ;

    /**
     * @ngInject
     */
    function routeConfig(RouteHelpersProvider) {
        var helper = RouteHelpersProvider;
        var pkg = helper.package('backup');
        pkg
            .state('recurring.list', {
                url: '',
                title: 'Recurrings',
                controller: 'PkgBackupRecurringIndexCtrl as vm',
                templateUrl: pkg.asset('admin/recurring/list/list.index.html'),
            })
        ;
    }

})();
