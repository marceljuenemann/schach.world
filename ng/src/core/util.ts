
// Utility function to download data as CSV.
export function downloadCsv(rows: any[][], filename: (data: any[][]) => string) {
  const csvContent = rows.map(e => e.map((v: any) => {
    if (v === null || v === undefined) return '';
    v = v.toString();
    if (v.includes('"') || v.includes(',') || v.includes('\n')) {
      v = `"${v.replace(/"/g, '""')}"`;
    }
    return v;
  }).join(",")).join("\n");

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement("a");
  const url = URL.createObjectURL(blob);
  link.setAttribute("href", url);
  link.setAttribute("download", filename(rows));
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
