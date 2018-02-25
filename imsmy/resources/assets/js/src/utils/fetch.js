//fetch
import {hashHistory} from 'react-router'
import { message } from 'antd'

export default {
	post:(opt)=>{
			let TOKEN = localStorage.getItem("TOKEN")
			fetch(opt.uri,{
				method:"POST",
				headers:{
					// 'Content-Type': 'application/x-www-form-urlencoded',
					Authorization:'Bearer ' + TOKEN,
					// Referer :"http://www.hivideo.com/",
				},
				body:opt.formData
			})
			.then((response)=> response.json())
			.then((res)=>{
				// if(res.error && res.error==='token_not_provided'){
				// 	message.info('请您登录')
				// 	hashHistory.push('/login')
        //
				// }else
				if(res.error && res.error==='token_expired'){
						message.info('认证已过期,请重新登录')
						localStorage.removeItem('TOKEN')
						hashHistory.push('/login')
				}else{
					opt.callback(res)
				}
			})
			.catch((err) => {console.log(err)})
		}



}
