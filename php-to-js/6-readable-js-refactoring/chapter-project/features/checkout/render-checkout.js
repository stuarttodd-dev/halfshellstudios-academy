export function showSuccess(ui) {
  ui.successVisible = true;
  ui.errorMessage = '';
}

export function showError(ui, message) {
  ui.successVisible = false;
  ui.errorMessage = message;
}
