	//时间转换 - formatSec  时间戳转换 - formatStamp
export default {
	formatSec:(timeValue)=>{
			let hh = parseInt(timeValue/3600)
	    let mm = parseInt((timeValue-hh*3600)/60)
	    let ss = parseInt((timeValue-hh*3600)%60)
	      if(hh<10) hh = "0" + hh
	      if(mm<10) mm = "0" + mm
	      if(ss<10) ss = "0" + ss
	    let timeOne = mm + ":" +ss
	    let timeTwo = hh + ":" + mm + ":" + ss
	      if(timeValue>0 && timeValue<3600){
	        return timeOne
	      }if(timeValue>=3600){
	        return timeTwo
	      }else{
	          return "00:00"
	      }
		},
	formatStamp:(dateX)=>{
		let stamp = new Date(parseInt(dateX) * 1000)
		let year = stamp.getFullYear()
		let month = (stamp.getMonth() +1) <10 ? '0' +(stamp.getMonth() +1) : (stamp.getMonth() +1)
		let day = stamp.getDate() <10 ? '0' + stamp.getDate() : stamp.getDate()
		let hour = stamp.getHours()<10 ? '0' + stamp.getHours() : stamp.getHours()
		let min = stamp.getMinutes()< 10 ? '0' + stamp.getMinutes() : stamp.getMinutes()
		let sec = stamp.getSeconds()< 10 ? '0' + stamp.getSeconds() : stamp.getSeconds()
		let StampTime = year + '-' + month + '-'+day + ' ' + hour + ':' + min + ':' + sec
		return StampTime
	},
	NowTime:()=>{
		let NowTime=new Date()
		let NowYeall = NowTime.getFullYear()
		let NowMonth = (NowTime.getMonth()+1)<10 ? '0' +(NowTime.getMonth()+1) : (NowTime.getMonth() +1)
		let NowDay = NowTime.getDate()<10 ? '0' + NowTime.getDate() : NowTime.getDate()
		let NowHours = NowTime.getHours()<10 ? '0' + NowTime.getHours() : NowTime.getHours()
		let NowMinutes = NowTime.getMinutes()< 10 ? '0' + NowTime.getMinutes() : NowTime.getMinutes()
		let NowSeconds = NowTime.getSeconds()< 10 ? '0' + NowTime.getSeconds() : NowTime.getSeconds()
		let NowTimes = NowYeall+""+NowMonth+""+NowDay+""+NowHours+""+NowMinutes+""+NowSeconds
		return NowTimes
	},
	uploadTime:(data)=>{
		let NowTimes = parseInt(data /1000);
		if(NowTimes<60){
			if(NowTimes<10){
				return '0'+NowTimes+'秒'
			}else{
				return NowTimes+'秒'
			}
		}else if(NowTimes>=60 && NowTimes<3600){
			let timeSec = (NowTimes%60)<10? '0'+(NowTimes%60)+'秒' : (NowTimes%60)+'秒'
			let timeMin = parseInt(NowTimes/60)<10? '0'+ parseInt(NowTimes/60)+'分' : parseInt(NowTimes/60)+'分'
			return timeMin+timeSec
		}else if(NowTimes>3600){
			let timeHours = parseInt(NowTimes/3600)
			let timeMinutes = parseInt((NowTimes-timeHours*3600)/60)
			let timeSeconds =parseInt((NowTimes-timeHours*3600)%60)
				if(timeHours<10) {
					timeHours='0'+timeHours+'小时'
				}else{
					timeHours = timeHours+'小时'
				}
				if(timeMinutes<10){
					timeMinutes='0'+timeMinutes+'分'
				}else{
					timeMinutes=timeMinutes+'分'
				}
				if(timeSeconds<10){
					timeSeconds='0'+timeSeconds+'秒'
				} else{
					timeSeconds=timeSeconds+'秒'
				}
			return timeHours+timeMinutes+timeSeconds
		}
	}
}
