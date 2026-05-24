import { Router } from 'express';
import { requireAdmin } from '../middleware/auth.js';
import { toProductCollection, toProductResource } from '../resources/productResource.js';
import * as productService from '../services/productService.js';
import { validateStoreProduct, validateUpdateProduct } from '../validation/productValidation.js';

const router = Router();

router.get('/', (req, res) => {
  const page = Number(req.query.page ?? 1);
  const q = String(req.query.q ?? '');
  const result = productService.listProducts({ page, q });
  res.json(toProductCollection(result));
});

router.get('/:id', (req, res) => {
  const product = productService.findProduct(req.params.id);
  if (!product) {
    return res.status(404).json({ message: 'Product not found.' });
  }
  res.json({ data: toProductResource(product) });
});

router.post('/', requireAdmin, (req, res) => {
  const errors = validateStoreProduct(req.body);
  if (Object.keys(errors).length > 0) {
    return res.status(422).json({ message: 'Validation failed.', errors });
  }
  const product = productService.createProduct(req.body);
  res.status(201).json({ data: toProductResource(product) });
});

router.patch('/:id', requireAdmin, (req, res) => {
  const errors = validateUpdateProduct(req.body);
  if (Object.keys(errors).length > 0) {
    return res.status(422).json({ message: 'Validation failed.', errors });
  }
  const product = productService.updateProduct(req.params.id, req.body);
  if (!product) {
    return res.status(404).json({ message: 'Product not found.' });
  }
  res.json({ data: toProductResource(product) });
});

router.delete('/:id', requireAdmin, (req, res) => {
  const deleted = productService.deleteProduct(req.params.id);
  if (!deleted) {
    return res.status(404).json({ message: 'Product not found.' });
  }
  res.status(204).send();
});

export default router;
