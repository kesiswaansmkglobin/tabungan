import { router } from '@inertiajs/react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData {
    data: any[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
}

export default function Pagination({ current_page, last_page, from, to, total, links }: PaginatedData) {
    if (last_page <= 1) return null;

    function visit(url: string | null) {
        if (url) {
            router.get(url, {}, { preserveState: true, preserveScroll: true });
        }
    }

    function visibleLinks() {
        const start = Math.max(1, current_page - 2);
        const end = Math.min(last_page, current_page + 2);

        return links.filter((link) => {
            const label = link.label.replace(/&laquo;|&raquo;/g, '').trim();
            const pageNum = parseInt(label);

            if (isNaN(pageNum)) return true;
            return pageNum >= start && pageNum <= end;
        });
    }

    return (
        <div className="flex flex-col items-center gap-2 px-4 py-3 sm:flex-row sm:justify-between">
            <p className="text-sm text-gray-600 dark:text-gray-400">
                Menampilkan {from}–{to} dari {total}
            </p>

            <nav className="flex items-center gap-1">
                {visibleLinks().map((link, i) => {
                    const label = link.label
                        .replace(/&laquo; Previous/g, '‹')
                        .replace(/&laquo; Sebelumnya/g, '‹')
                        .replace(/Next &raquo;/g, '›')
                        .replace(/Selanjutnya &raquo;/g, '›')
                        .replace(/&laquo;/g, '‹')
                        .replace(/&raquo;/g, '›')
                        .trim();

                    const isPrevNext = label === '‹' || label === '›';

                    return (
                        <button
                            key={i}
                            disabled={!link.url}
                            onClick={() => visit(link.url)}
                            className={`flex min-w-[32px] items-center justify-center rounded-lg px-2 py-1.5 text-sm font-medium transition
                                ${link.active
                                    ? 'bg-navy-500 text-white dark:bg-navy-400'
                                    : link.url
                                        ? 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-navy-700'
                                        : 'cursor-not-allowed text-gray-400 dark:text-gray-600'
                                }
                                ${isPrevNext ? 'px-3' : ''}
                            `}
                        >
                            {label}
                        </button>
                    );
                })}
            </nav>
        </div>
    );
}
