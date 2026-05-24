const ADMIN_TOKEN = 'admin-token';
const MEMBER_TOKEN = 'member-token';

export function requireAuth(req, res, next) {
  const header = req.headers.authorization ?? '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : null;

  if (!token) {
    return res.status(401).json({ message: 'Unauthenticated.' });
  }

  if (token === ADMIN_TOKEN) {
    req.user = { id: 1, role: 'admin' };
    return next();
  }

  if (token === MEMBER_TOKEN) {
    req.user = { id: 2, role: 'member' };
    return next();
  }

  return res.status(401).json({ message: 'Unauthenticated.' });
}

export function requireAdmin(req, res, next) {
  if (req.user?.role !== 'admin') {
    return res.status(403).json({ message: 'Forbidden.' });
  }
  next();
}

export const tokens = { ADMIN_TOKEN, MEMBER_TOKEN };
