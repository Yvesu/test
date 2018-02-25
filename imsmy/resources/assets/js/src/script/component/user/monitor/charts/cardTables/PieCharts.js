import React,{ Component } from 'react'
import { Pie } from 'ant-design-pro/lib/Charts'
import Fetch from 'utils/fetch'

class PieCharts extends Component{
  constructor(props){
    super(props)
    this.state={
      iosUser:'',
      androidUser:'',
      webUser:'',
      userOther:''
    }
  }
  render(){
    const salesPieData = [
      {
        x: 'iOS',
        y: parseInt(this.state.iosUser),
      },
      {
        x: 'Android',
        y: parseInt(this.state.androidUser),
      },
      {
        x: 'WEB',
        y: parseInt(this.state.webUser),
      },
      {
        x: '游客',
        y: parseInt(this.state.userOther),
      }
    ]
    return(
      <div className="charts_pie_box">
        <Pie
         hasLegend
         title="活跃量"
         subTitle="活跃量"
         total={(salesPieData.reduce((pre, now) => now.y + pre, 0)).toLocaleString()}
         data={salesPieData}
         valueFormat={val => (val).toLocaleString()}
         height={180}

         // inner={0.55}
         // color={'#ff0000'}
         // percent={50}
       />
      </div>
    )
  }
  componentDidMount(){
    Fetch.post({
      uri:'/api/admins/user/supervisory/index',
      callback:(res)=>{
        // console.log(res,'bing');
        this.setState({
          // iosUser:res.activeIosSum,
          // androidUser:res.activeAndroidSum,
          // webUser:res.activeWebSum,
          // userOther:res.activeSum
          iosUser:'1200',
          androidUser:"1500",
          webUser:"1900",
          userOther:"5000"
        })
      }
    })
  }
}

export default PieCharts
