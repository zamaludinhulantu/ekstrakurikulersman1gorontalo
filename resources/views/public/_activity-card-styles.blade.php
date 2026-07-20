        .public-activity-card {
            height: 100%;
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(248, 251, 255, 0.95));
            box-shadow: 0 14px 26px rgba(16, 35, 63, 0.06);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.24s ease, box-shadow 0.24s ease, border-color 0.24s ease;
        }

        .public-activity-card.is-extracurricular {
            border-top: 4px solid #1f5eff;
        }

        .public-activity-card.is-osn {
            border-top: 4px solid #0f9bd7;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(243, 250, 255, 0.96));
        }

        .public-activity-card.is-o2sn {
            border-top: 4px solid #d99019;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(255, 250, 242, 0.96));
        }

        .public-activity-card-media {
            aspect-ratio: 16 / 9;
            background: #eef5ff;
            overflow: hidden;
        }

        .public-activity-card-image,
        .public-activity-card-fallback {
            width: 100%;
            height: 100%;
            display: block;
        }

        .public-activity-card-image {
            object-fit: cover;
            transition: transform 0.35s ease;
        }

        .public-activity-card-fallback {
            background: linear-gradient(135deg, #dfeeff 0%, #eef5ff 50%, #d9e9ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .public-activity-card-fallback-inner {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            width: 100%;
        }

        .public-activity-card-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.76);
            color: #355987;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .public-activity-card-kicker {
            display: block;
            color: #5d789a;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.15rem;
        }

        .public-activity-card-fallback-title {
            display: block;
            color: #23446f;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .public-activity-card-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
        }

        .public-activity-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-bottom: 0.7rem;
        }

        .public-activity-card-category {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            margin-bottom: 0.55rem;
            padding: 0.34rem 0.62rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 900;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .public-activity-card.is-extracurricular .public-activity-card-category {
            background: #eaf2ff;
            color: #1849cb;
        }

        .public-activity-card.is-osn .public-activity-card-category {
            background: #eaf8ff;
            color: #0d78a7;
        }

        .public-activity-card.is-o2sn .public-activity-card-category {
            background: #fff4dd;
            color: #a76405;
        }

        .public-activity-card-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.38rem 0.62rem;
            border-radius: 999px;
            background: #eef5ff;
            color: #355987;
            font-size: 0.73rem;
            font-weight: 800;
        }

        .public-activity-card-meta .is-open {
            background: #ecfdf3;
            color: #0f7a49;
        }

        .public-activity-card-meta .is-closed {
            background: #fff1f2;
            color: #b42318;
        }

        .public-activity-card-title {
            margin: 0 0 0.4rem;
            font-size: 1.05rem;
            font-weight: 800;
            color: #163252;
            min-height: 2.7rem;
        }

        .public-activity-card-description {
            margin: 0 0 0.9rem;
            color: #5a6d84;
            font-size: 0.9rem;
            line-height: 1.65;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 4.4rem;
        }

        .public-activity-card-info {
            display: grid;
            gap: 0.55rem;
            margin-bottom: 1rem;
        }

        .public-activity-card-info div {
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
            color: #4d617b;
            font-size: 0.86rem;
            line-height: 1.55;
        }

        .public-activity-card-info i {
            color: #1f5eff;
            font-size: 0.86rem;
            margin-top: 0.18rem;
            flex-shrink: 0;
        }

        .public-activity-card.is-osn .public-activity-card-info i {
            color: #0f9bd7;
        }

        .public-activity-card.is-o2sn .public-activity-card-info i {
            color: #d99019;
        }

        .public-activity-card-info span {
            color: #23446f;
        }

        .public-activity-card-actions {
            margin-top: auto;
            display: flex;
            gap: 0.65rem;
        }

        .public-activity-card-actions .btn {
            flex: 1 1 0;
        }

        .public-activity-card:hover {
            transform: translateY(-5px);
            border-color: #c7d8ef;
            box-shadow: 0 20px 34px rgba(16, 35, 63, 0.1);
        }

        .public-activity-card:hover .public-activity-card-image {
            transform: scale(1.03);
        }
