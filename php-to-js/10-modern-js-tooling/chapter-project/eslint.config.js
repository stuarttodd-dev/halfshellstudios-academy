import js from '@eslint/js';
import globals from 'globals';

export default [
  js.configs.recommended,
  {
    files: ['resources/js/**/*.js'],
    ignores: ['**/*.test.js'],
    languageOptions: {
      globals: {
        ...globals.browser,
      },
    },
    rules: {
      'no-unused-vars': 'error',
      eqeqeq: 'error',
    },
  },
];
