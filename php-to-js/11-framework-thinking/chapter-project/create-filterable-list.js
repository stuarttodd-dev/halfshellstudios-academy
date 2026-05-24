import { visibleItems } from './visible-items.js';

export function createFilterableList({ getState, onFilter }) {
  return {
    setFilter(status) {
      onFilter(status);
    },
    render() {
      return visibleItems(getState()).map((item) => item.name);
    },
    destroy() {},
  };
}
