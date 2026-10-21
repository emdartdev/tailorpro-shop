// Pure helpers describing how many cutting-slip pieces (Shirt / Pant) a single
// order item should produce. These rules mirror the slip-entry expansion in
// `pdf.js` (both `generateSlipPDF` and `generateBulkCuttingSlipPDF`).

export const OFFICER_SHIRT_ONLY_ITEMS = new Set([
  "half sleeve shirt", "full sleeve shirt", "punjabi", "jubba", "blazer",
  "meskit shirt", "koti", "dust coat", "mission genji", "fotua",
  "civil shirt", "half bush upor part",
]);

export const OFFICER_PANT_ONLY_ITEMS = new Set([
  "uniform pant", "civil pant", "paijama",
]);

export const normalizeItemKey = (s) =>
  String(s || "").toLowerCase().replace(/[_-]+/g, " ").replace(/\s+/g, " ").trim();

/**
 * Compute the number of Shirt / Pant slip pieces a single order item should produce.
 */
export function computeSlipPieceCount(item, isAirman, mode) {
  const itemLabel = item.item_name
    || (item.item_type ? item.item_type.charAt(0).toUpperCase() + item.item_type.slice(1) : "Item");

  const rawQty = Math.max(1, Number(item.quantity || 1));
  const qty = isAirman ? rawQty : (mode === "batch" ? 1 : rawQty);

  if (isAirman) {
    const isFullSleeve = (itemLabel || "").toLowerCase().includes("full sleeve");
    const shirt = item.hasShirtMeas ? qty : 0;
    const pant = !isFullSleeve && item.hasPantMeas ? qty : 0;
    return { shirt, pant, total: shirt + pant };
  }

  const keys = [itemLabel, item.item_name, item.item_type].map(normalizeItemKey);
  const isShirtOnly = keys.some(k => k && OFFICER_SHIRT_ONLY_ITEMS.has(k));
  const isPantOnly = keys.some(k => k && OFFICER_PANT_ONLY_ITEMS.has(k));

  const shirt = item.hasShirtMeas && !isPantOnly ? qty : 0;
  const pant = item.hasPantMeas && !isShirtOnly ? qty : 0;
  return { shirt, pant, total: shirt + pant };
}

export function computeOrderSlipCount(items, isAirman, mode) {
  return (items || []).reduce((acc, it) => {
    const c = computeSlipPieceCount(it, isAirman, mode);
    return { shirt: acc.shirt + c.shirt, pant: acc.pant + c.pant, total: acc.total + c.total };
  }, { shirt: 0, pant: 0, total: 0 });
}
