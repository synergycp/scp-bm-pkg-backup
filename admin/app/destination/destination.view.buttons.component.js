(function () {
    'use strict';

    angular
        .module('pkg.backup.destination')
        .component('destinationButtons', {
            require: {},
            bindings: {
                destination: '=',
            },
            controller: 'DestinationButtonsCtrl as buttons',
            transclude: true,
            templateUrl: viewTemplateUrl
        })
        .controller('DestinationButtonsCtrl', DestinationButtonsCtrl);

    /**
     * @ngInject
     */
    function viewTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/destination/destination.view.buttons.html')
            ;
    }

    /**
     * @ngInject
     */
    function DestinationButtonsCtrl(DestinationList, Loader, $state) {
        var buttons = this;

        buttons.loader = Loader();
        buttons.$onInit = init;
        buttons.delete = doDelete;


        //////////

        function init() {

        }

        function doDelete() {
            return buttons.loader.during(
                DestinationList()
                    .confirm
                    .delete([buttons.destination])
                    .result.then(transferToList)
            );
        }

        function transferToList() {
            $state.go('app.pkg.backup.destination.list');
        }
    }
})();
