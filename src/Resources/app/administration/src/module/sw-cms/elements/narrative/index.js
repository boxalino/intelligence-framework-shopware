import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'narrative',
    label: 'Boxalino Narrative',
    component: 'sw-cms-el-narrative',
    configComponent: 'sw-cms-el-config-narrative',
    previewComponent: 'sw-cms-el-preview-narrative',
    defaultConfig: {
        widget: {
            source: 'static',
            value: 'navigation',
            required: true
        },
        hitCount: {
            source: 'static',
            value: 1,
            required: true
        },
        returnFields: {
            source: 'static',
            value: null
        },
        groupBy: {
            source: 'static',
            value: 'id',
            required: true
        },
        filters: {
            source: 'static',
            value: null
        },
        facets: {
            source: 'static',
            value: null
        },
        context: {
            source: 'static',
            value: 'listing'
        }
    }
});
