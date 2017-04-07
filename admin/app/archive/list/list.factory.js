(function () {
  'use strict';

  angular
    .module('pkg.backup.archive.list')
    .factory('ArchiveList', ArchiveListFactory);

  /**
   * ArchiveList Factory
   *
   * @ngInject
   */
  function ArchiveListFactory (List, ListConfirm, ArchiveAssign) {
    return function () {
      var list = List('archive');
      list.confirm = ListConfirm(list, 'archive.modal.delete');

      list.bulk.add('Assign IP Group', wrapChangeEvent(ArchiveAssign.group));
      list.bulk.add('Delete', list.confirm.delete);

      return list;

      function wrapChangeEvent(callback) {
        return function () {
          return callback.apply(null, arguments).then(fireChangeEvent);
        };
      }

      function fireChangeEvent(arg) {
        list.fire('change', arg);

        return arg;
      }
    };
  }
})();
