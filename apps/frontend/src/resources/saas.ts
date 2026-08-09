import type { ResourceConfig } from './types';
export const saasResources: ResourceConfig[]=[
 {key:'tenant-modules',label:'Client Modules',labelAr:'موديولات العميل',endpoint:'/v1/tenant/modules',icon:'🧱',columns:[{key:'module',label:'Module',labelAr:'الموديول'},{key:'edition',label:'Edition',labelAr:'الإصدار'},{key:'is_enabled',label:'Enabled',labelAr:'مفعل',type:'boolean'}]},
 {key:'tenant-requirements',label:'Client Requirements',labelAr:'احتياجات العميل',endpoint:'/v1/tenant/requirements',icon:'🎯',columns:[{key:'category',label:'Category',labelAr:'التصنيف'},{key:'key',label:'Key',labelAr:'المفتاح'},{key:'requirement',label:'Requirement',labelAr:'الاحتياج'},{key:'priority',label:'Priority',labelAr:'الأولوية'},{key:'status',label:'Status',labelAr:'الحالة'}]},
 {key:'subscriptions',label:'Subscription',labelAr:'الاشتراك',endpoint:'/v1/tenant/subscription',icon:'💳',columns:[{key:'status',label:'Status',labelAr:'الحالة'},{key:'billing_cycle',label:'Cycle',labelAr:'الدورة'}]},
];
