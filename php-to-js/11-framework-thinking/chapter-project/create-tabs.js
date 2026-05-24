export function createTabs({ tabs, getActive, onSelect }) {
  return {
    select(tab) {
      onSelect(tab);
    },
    render() {
      return tabs
        .map((tab) => (tab === getActive() ? `[${tab}]` : tab))
        .join(' | ');
    },
    destroy() {},
  };
}
