(function () {
  'use strict';

  angular
    .module('pkg.backup.destination.list.filters')
    .controller('DestinationFiltersCtrl', DestinationFiltersCtrl)
  ;

  /**
   * @ngInject
   */
  function DestinationFiltersCtrl(Select, Observable, $state, $q) {
    var filters = this;

    filters.$onInit = init;
    filters.$onChanges = $onChanges;

    filters.current = {q: $state.params.q};
    filters.status = Select()
      .addItem({
        id: '0',
        text: 'Queued',
        name: 'Queued'
      })
      .addItem({
        id: 1,
        text: 'Compressing',
        name: 'Compressing'
      })
      .addItem({
        id: 2,
        text: 'Copying Off-site',
        name: 'Copying Off-site'
      })
      .addItem({
        id: 4,
        text: 'Finished',
        name: 'Finished'
      })
      .addItem({
        id: 10,
        text: 'Failed',
        name: 'Failed'
      })
    ;

    filters.searchFocus = Observable(false);

    filters.fireChangeEvent = fireChangeEvent;

    //////////

    function init() {
      $q.all([
          filters.status.setSelectedId($state.params['status.id']),
        ])
        .then(listenForChanges)
        .then(fireChangeEvent);
    }

    function listenForChanges() {
      filters.status.on('change', fireChangeEvent);
    }

    function fireChangeEvent() {
      _.assign(filters.current, {
        status: filters.status.getSelected('id'),
      });

      $state.go($state.current.name, {
        'status.id': filters.current.status,
        q: filters.current.q,
      });

      if (filters.change) {
        filters.change();
      }
    }

    function $onChanges(changes) {
      if (changes.show) {
        var onShow = filters.searchFocus.set.bind(null, true);
        (changes.show.currentValue ? onShow : angular.noop)();
      }
    }
  }
})();
