(function () {
    'use strict';

    angular
        .module('pkg.backup.recurring')
        .component('recurringButtons', {
            require: {},
            bindings: {
                recurring: '=',
            },
            controller: 'RecurringButtonsCtrl as buttons',
            transclude: true,
            templateUrl: viewTemplateUrl
        })
        .controller('RecurringButtonsCtrl', RecurringButtonsCtrl);

    /**
     * @ngInject
     */
    function viewTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/recurring/recurring.view.buttons.html')
            ;
    }

    /**
     * @ngInject
     */
    function RecurringButtonsCtrl(RecurringList, Loader, $state) {
        var buttons = this;

        buttons.loader = Loader();
        buttons.$onInit = init;
        buttons.delete = doDelete;


        //////////

        function init() {

        }

        function doDelete() {
            return buttons.loader.during(
                RecurringList()
                    .confirm
                    .delete([buttons.recurring])
                    .result.then(transferToList)
            );
        }

        function transferToList() {
            $state.go('app.pkg.backup.recurring.list');
        }
    }
})();
