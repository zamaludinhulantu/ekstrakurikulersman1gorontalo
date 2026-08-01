@once
    @push('styles')
        <style>
            .article-management-page,
            .article-management-page > *,
            .article-management-list,
            .article-management-filter,
            .article-management-table-wrap {
                min-width: 0;
                max-width: 100%;
            }

            .article-management-page {
                width: calc(100% - 1.5rem);
                margin: .75rem auto 1.5rem;
            }

            .article-management-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: .75rem;
            }

            .article-management-toolbar h2 {
                margin: 0;
                color: #13294b;
                font-size: 1.08rem;
                font-weight: 800;
            }

            .article-management-toolbar p {
                margin: .2rem 0 0;
                color: #667b99;
                font-size: .78rem;
            }

            .article-management-toolbar__actions {
                display: flex;
                flex: 0 0 auto;
                gap: .55rem;
            }

            .article-management-stats {
                display: grid;
                grid-template-columns: repeat(6, minmax(0, 1fr));
                gap: .65rem;
                margin-bottom: .75rem;
            }

            .article-management-stat {
                min-height: 4.15rem;
                padding: .8rem .95rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .5rem;
                border: 1px solid #dce6f3;
                border-bottom: 2px solid #94a3b8;
                border-radius: 1rem;
                color: #536986;
                background: #fff;
                text-decoration: none;
                box-shadow: 0 7px 18px rgba(42, 71, 108, .05);
            }

            .article-management-stat span {
                font-size: .72rem;
                font-weight: 700;
                line-height: 1.25;
            }

            .article-management-stat strong {
                color: #12294b;
                font-size: 1.15rem;
                font-weight: 800;
            }

            .article-management-stat.is-draft { border-bottom-color: #f59e0b; }
            .article-management-stat.is-scheduled { border-bottom-color: #3b82f6; }
            .article-management-stat.is-published { border-bottom-color: #10b981; }
            .article-management-stat.is-archived { border-bottom-color: #64748b; }
            .article-management-stat.is-inactive { border-bottom-color: #ef4444; }

            .article-management-list {
                overflow: hidden;
            }

            .article-management-filter {
                padding: .85rem 1rem;
                border-bottom: 1px solid #e3ebf5;
                background: #fff;
            }

            .article-management-filter__main {
                display: grid;
                grid-template-columns: minmax(14rem, 2fr) minmax(10rem, 1fr) minmax(10rem, 1fr) auto auto auto;
                align-items: end;
                gap: .6rem;
            }

            .article-management-filter__advanced {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(9.5rem, 1fr));
                gap: .65rem;
                margin-top: .75rem;
                padding-top: .75rem;
                border-top: 1px dashed #dce6f3;
            }

            .article-management-filter .form-label {
                margin-bottom: .28rem;
                color: #536986;
                font-size: .7rem;
                font-weight: 750;
            }

            .article-management-filter .form-control,
            .article-management-filter .form-select,
            .article-management-filter .btn {
                min-height: 2.55rem;
                font-size: .78rem;
            }

            .article-management-filter__main > .btn {
                height: 2.55rem;
                padding: .45rem .72rem;
                white-space: nowrap;
            }

            .article-management-chips {
                display: flex;
                flex-wrap: wrap;
                gap: .4rem;
                padding: .65rem 1rem;
                border-bottom: 1px solid #e3ebf5;
                background: #f8fbff;
            }

            .article-management-chip {
                padding: .28rem .6rem;
                border: 1px solid #cfe0f6;
                border-radius: 999px;
                color: #31567d;
                background: #fff;
                font-size: .7rem;
                font-weight: 700;
            }

            .article-management-table-wrap {
                width: 100%;
                overflow-x: auto;
                scrollbar-width: thin;
            }

            .article-management-table {
                width: 100%;
                min-width: 40.625rem;
                margin: 0;
                table-layout: fixed;
            }

            .article-management-table th {
                padding: .7rem .75rem;
                color: #56708e;
                background: #f2f7fd;
                font-size: .67rem;
                font-weight: 800;
                letter-spacing: .03em;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .article-management-table td {
                padding: .7rem .75rem;
                color: #243b5a;
                font-size: .76rem;
                vertical-align: middle;
                border-color: #e7edf5;
            }

            .article-management-table th:nth-child(1) { width: 30%; }
            .article-management-table th:nth-child(2) { width: 23%; }
            .article-management-table th:nth-child(3) { width: 22%; }
            .article-management-table th:nth-child(4) { width: 17%; }
            .article-management-table th:nth-child(5) { width: 8%; }

            .article-management-content {
                display: flex;
                align-items: center;
                gap: .7rem;
                min-width: 0;
            }

            .article-management-thumb-wrap {
                width: 4.25rem;
                height: 3rem;
                flex: 0 0 auto;
                overflow: hidden;
                display: grid;
                place-items: center;
                border-radius: .65rem;
                color: #3974dc;
                background: #eaf2ff;
            }

            .article-management-thumb {
                width: 100%;
                height: 100%;
                display: block;
                object-fit: cover;
            }

            .article-management-content__copy,
            .article-management-meta {
                min-width: 0;
            }

            .article-management-content__copy strong,
            .article-management-content__copy span {
                display: -webkit-box;
                overflow: hidden;
                -webkit-box-orient: vertical;
            }

            .article-management-content__copy strong {
                display: block;
                color: #172c4b;
                font-size: .78rem;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .article-management-content__copy span {
                margin-top: .2rem;
                color: #71839d;
                font-size: .69rem;
                line-height: 1.35;
                -webkit-line-clamp: 2;
            }

            .article-management-meta strong,
            .article-management-meta span {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .article-management-meta span {
                margin-top: .18rem;
                color: #71839d;
                font-size: .68rem;
            }

            .article-management-table td > small {
                display: block;
                margin-top: .18rem;
                color: #71839d;
                font-size: .68rem;
                line-height: 1.3;
            }

            .article-management-status {
                display: inline-flex;
                padding: .28rem .55rem;
                border: 1px solid #cddaea;
                border-radius: 999px;
                color: #536986;
                background: #f4f7fb;
                font-size: .67rem;
                font-weight: 750;
                white-space: nowrap;
            }

            .article-management-status[data-status="published"] { color: #087b55; border-color: #bdebd9; background: #eafbf4; }
            .article-management-status[data-status="scheduled"] { color: #1d5bc3; border-color: #c5dcff; background: #edf5ff; }
            .article-management-status[data-status="draft"] { color: #a45c00; border-color: #f9d99b; background: #fff8e8; }
            .article-management-status[data-status="archived"],
            .article-management-status[data-status="inactive"] { color: #526477; border-color: #d8e0e9; background: #f2f5f8; }
            .article-management-status[data-status="expired"] { color: #b4232c; border-color: #fac9cd; background: #fff0f1; }

            .article-management-pagination {
                min-height: 3.5rem;
                padding: .75rem 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                color: #687d99;
                font-size: .72rem;
                border-top: 1px solid #e3ebf5;
            }

            .article-management-mobile {
                display: none;
            }

            @media (max-width: 1199.98px) {
                .article-management-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                .article-management-filter__main { grid-template-columns: minmax(13rem, 2fr) repeat(2, minmax(9rem, 1fr)); }
            }

            @media (max-width: 767.98px) {
                .article-management-page { width: calc(100% - 1rem); margin-top: .5rem; }
                .article-management-toolbar { align-items: flex-start; flex-direction: column; }
                .article-management-toolbar__actions { width: 100%; }
                .article-management-toolbar__actions .btn { flex: 1; }
                .article-management-stats {
                    display: flex;
                    overflow-x: auto;
                    padding-bottom: .25rem;
                    scroll-snap-type: x proximity;
                }
                .article-management-stat { min-width: 9rem; scroll-snap-align: start; }
                .article-management-filter__main,
                .article-management-filter__advanced { grid-template-columns: 1fr; }
                .article-management-table-wrap { display: none; }
                .article-management-mobile {
                    display: grid;
                    gap: .65rem;
                    padding: .75rem;
                }
                .article-management-mobile .mobile-data-card {
                    padding: .8rem;
                    border: 1px solid #dce6f3;
                    border-radius: .9rem;
                    background: #fff;
                }
                .article-management-mobile__head,
                .article-management-mobile .mobile-data-card-actions {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: .65rem;
                }
                .article-management-mobile__head > div { min-width: 0; }
                .article-management-mobile__head h3 {
                    margin: 0;
                    color: #172c4b;
                    font-size: .85rem;
                    font-weight: 800;
                }
                .article-management-mobile__head p {
                    margin: .2rem 0 0;
                    color: #71839d;
                    font-size: .72rem;
                }
                .article-management-mobile__image {
                    width: 4.5rem;
                    height: 3.25rem;
                    flex: 0 0 auto;
                    object-fit: cover;
                    border-radius: .65rem;
                }
                .article-management-mobile__meta {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    gap: .4rem .6rem;
                    margin-top: .75rem;
                    padding-top: .65rem;
                    border-top: 1px solid #e7edf5;
                    color: #6b7f9b;
                    font-size: .7rem;
                }
                .article-management-mobile .mobile-data-card-actions { margin-top: .65rem; }
                .article-management-pagination { align-items: flex-start; flex-direction: column; }
            }
        </style>
    @endpush
@endonce
