import React, { Component } from 'react'
import { Card, Col, Row } from 'antd'
import MonitorCharts from './charts/MonitorCharts'
import CardProgress from './cardProgress/CardProgress'
import Fetch from 'utils/fetch'

//监控页

class UserMonitor extends Component {
  constructor(props){
    super(props)
    this.state={
      todayAdd:'',
      todayGirl:'',
      todayBoy:'',
      enroll:'',
      enrollGirl:'',
      enrollBoy:'',
      phoneUser:'',
      phoneUserScale:'',
    }
  }
  render(){
    return(
      <div className="user_monitor_box">
         <Row gutter={24}>
           <Col span={15}>
             <Card title="活动实时交易情况" className="monitor_title_content_box">
               <p>
                  <h3>
                    <span>今日新增</span>
                    <span>女：<b>{this.state.todayGirl}</b></span>
                    <span> 男：<b>{this.state.todayBoy}</b></span>
                  </h3>
                  <p><span style={{color:'#ff0000'}}>{this.state.todayAdd}</span><em>位</em></p>
               </p>
               <p>
                 <h3>
                   <span>注册用户 </span>
                   <span>女：<b>{this.state.enrollGirl}</b></span>
                   <span>  男：<b>{this.state.enrollBoy}</b></span>
                 </h3>
                 <p><span style={{color:'#ff0000'}}>{this.state.enroll}</span><em>位</em></p>
               </p>
               <p>
                 <h3>
                   <span>绑定手机用户<b>{this.state.phoneUserScale}</b></span>
                 </h3>
                 <p><span style={{color:'#ff0000'}}>{this.state.phoneUser}</span><em>位</em></p>
               </p>

             </Card>
           </Col>
           <Col span={9}>
             <Card title="项目上线" className="monitor_title_content_box">
                <b className="high_lines_time">632 天 00:12:12</b>
             </Card>
           </Col>
         </Row>

         <MonitorCharts />
         <CardProgress />

      </div>
    )
  }
  componentDidMount(){
    Fetch.post({
      uri:'/api/admins/user/supervisory/index',
      callback:(res)=>{
        // console.log(res,'jiankong ');
        this.setState({
          todayAdd:res.todayNewUser,
          todayGirl:res.todayNewUserWomen,
          todayBoy:res.todayNewUserMen,
          enroll:res.userNum,
          enrollGirl:res.womenUserNum,
          enrollBoy:res.menUserNum,
          phoneUser:res.phoneUserNum,
          phoneUserScale:res.phoneUserNumProportion,

        })
      }
    })
  }
}

export default UserMonitor
