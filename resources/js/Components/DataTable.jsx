import { useState, useMemo } from 'react';

export default function DataTable({ columns, data, footer, emptyMessage = "لا توجد بيانات متاحة" }) {
    const [sortConfig, setSortConfig] = useState({ key: null, direction: 'desc' });

    const sortedData = useMemo(() => {
        let sortableItems = [...data];
        if (sortConfig.key !== null) {
            sortableItems.sort((a, b) => {
                let aVal = a[sortConfig.key];
                let bVal = b[sortConfig.key];

                // Handle dates
                if (sortConfig.key.includes('date') || (typeof aVal === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(aVal))) {
                    return sortConfig.direction === 'asc' 
                        ? new Date(aVal) - new Date(bVal)
                        : new Date(bVal) - new Date(aVal);
                }

                // Handle numbers
                if (!isNaN(aVal) && !isNaN(bVal)) {
                    return sortConfig.direction === 'asc' ? aVal - bVal : bVal - aVal;
                }

                // Default string sort
                if (aVal < bVal) return sortConfig.direction === 'asc' ? -1 : 1;
                if (aVal > bVal) return sortConfig.direction === 'asc' ? 1 : -1;
                return 0;
            });
        }
        return sortableItems;
    }, [data, sortConfig]);

    const requestSort = (key) => {
        let direction = 'asc';
        if (sortConfig.key === key && sortConfig.direction === 'asc') {
            direction = 'desc';
        }
        setSortConfig({ key, direction });
    };

    return (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
                <table className="w-full text-right text-xs">
                    <thead>
                        <tr className="bg-slate-900 text-white font-bold">
                            {columns.map((col, i) => (
                                <th 
                                    key={i} 
                                    onClick={() => col.sortable !== false && requestSort(col.key)}
                                    className={`px-5 py-4 border-b border-slate-800 uppercase tracking-widest ${col.sortable !== false ? 'cursor-pointer hover:bg-slate-800 transition-colors' : ''} ${col.className || ''}`}
                                >
                                    <div className={`flex items-center gap-2 ${col.align === 'left' ? 'justify-end' : col.align === 'center' ? 'justify-center' : 'justify-start'}`}>
                                        {col.label}
                                        {col.sortable !== false && sortConfig.key === col.key && (
                                            <span className="text-[10px]">
                                                {sortConfig.direction === 'asc' ? '↑' : '↓'}
                                            </span>
                                        )}
                                    </div>
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {sortedData.length > 0 ? sortedData.map((row, rowIndex) => (
                            <tr key={rowIndex} className="hover:bg-slate-50/80 transition-colors group">
                                {columns.map((col, colIndex) => (
                                    <td 
                                        key={colIndex} 
                                        className={`px-5 py-3.5 ${col.align === 'left' ? 'text-left' : col.align === 'center' ? 'text-center' : 'text-right'} ${col.tdClassName || ''}`}
                                    >
                                        {col.render ? col.render(row) : row[col.key]}
                                    </td>
                                ))}
                            </tr>
                        )) : (
                            <tr>
                                <td colSpan={columns.length} className="py-20 text-center">
                                    <div className="flex flex-col items-center gap-2">
                                        <svg className="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        <span className="text-slate-400 font-bold uppercase tracking-widest">{emptyMessage}</span>
                                    </div>
                                </td>
                            </tr>
                        )}
                    </tbody>
                    {footer && (
                        <tfoot className="bg-slate-50 border-t-2 border-slate-200 font-black text-sm">
                            {footer}
                        </tfoot>
                    )}
                </table>
            </div>
        </div>
    );
}
