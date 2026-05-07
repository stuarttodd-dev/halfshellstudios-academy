// Requires: npm install zod
import { z } from 'zod';
import { useForm } from './useForm.js';

const schema = z.object({
  email:    z.string().email('Invalid email'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
  confirm:  z.string(),
}).refine(d => d.password === d.confirm, {
  message: 'Passwords do not match',
  path: ['confirm'],
});

const form = useForm(schema, { email: '', password: '', confirm: '' });

form.subscribe(state => {
  console.log('State update:', JSON.stringify(state));
});

form.setField('email', 'not-an-email');
form.setField('email', 'user@example.com');
form.setField('password', 'short');
form.setField('password', 'securepassword123');
form.setField('confirm', 'wrong');
form.setField('confirm', 'securepassword123');

await form.submit(async (data) => {
  console.log('Submitting valid data:', data);
  // await api.post('/register', data);
});
