/**
 * Downloads the given data as a JSON file.
 */
export function downloadJson(data: any, filename: string) {
  const jsonContent = JSON.stringify(data, null, 2);
  downloadFile(jsonContent, filename, 'application/json;charset=utf-8;');
}

/**
 * Utility function to download data as CSV.
 */
export function downloadCsv(rows: any[][], filename: (data: any[][]) => string) {
  const csvContent = rows.map(e => e.map((v: any) => {
    if (v === null || v === undefined) return '';
    v = v.toString();
    if (v.includes('"') || v.includes(',') || v.includes('\n')) {
      v = `"${v.replace(/"/g, '""')}"`;
    }
    return v;
  }).join(",")).join("\n");

  downloadFile(csvContent, filename(rows), 'text/csv;charset=utf-8;');
}

/**
 * Triggers a file download in the browser.
 */
export function downloadFile(content: string, filename: string, contentType: string) {
  const blob = new Blob([content], { type: contentType });
  const link = document.createElement("a");
  const url = URL.createObjectURL(blob);
  link.setAttribute("href", url);
  link.setAttribute("download", filename);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

/**
 * Uses localCompare for strings and normal comparison for numbers.
 */
export function genericCompare(a: any, b: any, order: 'asc' | 'desc', nullsFirst: boolean = false): number {
  return genericCompareAsc(a, b, nullsFirst) * (order === 'asc' ? 1 : -1);
}

function genericCompareAsc(a: any, b: any, nullsFirst: boolean): number {
  if (typeof a === 'number' && typeof b === 'number') {
    return a - b;
  } else if (a === null && b !== null) {
    return nullsFirst ? -1 : 1;
  } else if (a !== null && b === null) {
    return nullsFirst ? 1 : -1;
  }
  return String(a).localeCompare(String(b));
}
