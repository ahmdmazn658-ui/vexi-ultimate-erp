import type { ResourceConfig } from './types';
export const aiResources: ResourceConfig[]=[
 {key:'ai-insights',label:'AI Insights',labelAr:'رؤى الذكاء الاصطناعي',endpoint:'/v1/ai/insights',icon:'✨',columns:[{key:'module',label:'Module',labelAr:'الموديول'},{key:'type',label:'Type',labelAr:'النوع'},{key:'severity',label:'Severity',labelAr:'الأهمية'},{key:'title',label:'Title',labelAr:'العنوان'},{key:'is_read',label:'Read',labelAr:'مقروء',type:'boolean'}]},
 {key:'ai-capabilities',label:'AI Capabilities',labelAr:'قدرات الذكاء الاصطناعي',endpoint:'/v1/ai/capabilities',icon:'🤖',columns:[{key:'provider',label:'Provider',labelAr:'المزود'}]},
];
