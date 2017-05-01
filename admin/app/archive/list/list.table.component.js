(function () {
  'use strict';

  angular
    .module('pkg.backup.archive.list')
    .component('pkgBackupArchiveTable', {
      require: {
        list: '\^list',
      },
      bindings: {
        showSource: '=?',
        showDestination: '=?',
        showRecurring: '=?',
        showStatus: '=?',
        showCreatedAt: '=?',
      },
      controller: 'ArchiveTableCtrl as table',
      transclude: true,
      templateUrl: tableTemplateUrl
    })
    .controller('ArchiveTableCtrl', ArchiveTableCtrl)
  ;

  /**
   * @ngInject
   */
  function tableTemplateUrl(RouteHelpers) {
    return RouteHelpers
      .package('backup')
      .trustedAsset('admin/archive/list/list.table.html')
      ;
  }

  /**
   * @ngInject
   */
  function ArchiveTableCtrl() {
    var table = this;

    table.$onInit = init;

    ///////////

    function init() {
      _.defaults(table, {
        showSource: true,
        showDestination: true,
        showRecurring: true,
        showStatus: true,
        showCreatedAt: true,
      });
    }
  }
})();
