const baseconfig = {
    host: 'http://act.999house.com',
    imghost: 'http://act.999house.com',
    //host: 'http://999house.test.com',
    // imghost: 'http://999house.test.com',
    //sockethost:'ws://www.work.com/wbsocket',
    sitename: '999房产后台管理系统',

    ///production生产环境，development开发环境'/api',用于ajax请求连接自动补齐前面http网址，即该链接为 http网址+/api
    api_baseURL: process.env.NODE_ENV=='production'?'/999admin':'/999admin',  //api/admin
    isLoscal_debug: process.env.NODE_ENV=='production'?'0':'1',
}


module.exports = baseconfig
