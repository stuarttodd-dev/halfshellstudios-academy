import { describe, expect, it } from 'vitest';
import { greet } from './greet.js';

describe('greet', () => {
  it('returns a greeting', () => {
    expect(greet('Ada')).toBe('Hello, Ada');
  });

  it('throws when name is empty', () => {
    expect(() => greet('')).toThrow('name is required');
  });
});
