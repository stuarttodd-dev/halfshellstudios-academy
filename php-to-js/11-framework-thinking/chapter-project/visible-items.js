export function visibleItems(state) {
  return state.items.filter(
    (item) => state.filterStatus === 'all' || item.status === state.filterStatus,
  );
}
