/**
 * Shared, forgiving order search matcher used by Orders, Airman and Delivery
 * lists so all three behave identically on every device.
 */
const norm = (v) => String(v ?? "").toLowerCase().trim();
const digits = (v) => String(v ?? "").replace(/\D/g, "");

export function orderMatchesSearch(order, customer, rawSearch, items = []) {
  const s = norm(rawSearch);
  if (!s) return true;
  const sDigits = digits(s);

  const haystack = [
    order?.bd_number,
    order?.iv_number,
    order?.order_number,
    order?.receiver_name,
    customer?.name,
    customer?.phone,
    customer?.bd_number,
    ...items.flatMap((i) => [i?.item_name, i?.item_type, i?.iv_number]),
  ]
    .map(norm)
    .filter(Boolean);

  if (haystack.some((h) => h.includes(s))) return true;

  if (sDigits) {
    const numeric = [order?.order_number, order?.bd_number, order?.iv_number, customer?.phone, customer?.bd_number]
      .map(digits)
      .filter(Boolean);
    if (numeric.some((n) => n.includes(sDigits))) return true;
  }
  return false;
}
