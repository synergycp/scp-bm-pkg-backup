(function () {
    'use strict';

    angular
        .module('pkg.backup.source')
        .component('sourceButtons', {
            require: {},
            bindings: {
                source: '=',
            },
            controller: 'SourceButtonsCtrl as buttons',
            transclude: true,
            templateUrl: viewTemplateUrl
        })
        .controller('SourceButtonsCtrl', SourceButtonsCtrl);

    /**
     * @ngInject
     */
    function viewTemplateUrl(RouteHelpers) {
        return RouteHelpers
            .package('backup')
            .trustedAsset('admin/source/source.view.buttons.html')
            ;
    }

    /**
     * @ngInject
     */
    function SourceButtonsCtrl(SourceList, Loader, $state) {
        var buttons = this;

        buttons.loader = Loader();
        buttons.$onInit = init;
        buttons.delete = doDelete;


        //////////

        function init() {

        }

        function doDelete() {
            return buttons.loader.during(
                SourceList()
                    .confirm
                    .delete([buttons.source])
                    .result.then(transferToList)
            );
        }

        function transferToList() {
            $state.go('app.pkg.backup.source.list');
        }
    }
})();
