import { describe, expect, it } from 'vitest';
import { ApiError } from '../src/api/client.js';

describe('ApiError 422 mapping', () => {
  it('preserves Laravel-style field errors', () => {
    const error = new ApiError('Validation failed.', {
      status: 422,
      errors: { name: ['The name field is required.'] },
    });
    expect(error.status).toBe(422);
    expect(error.errors.name[0]).toContain('required');
  });
});
