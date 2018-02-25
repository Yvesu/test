import React, { Component } from 'react'
import { Layout } from 'antd'
import FetchPost from 'utils/fetch'
//管理信息
class Manage extends Component {
  constructor(props){
    super(props)
    this.Fetch=FetchPost.post
    this.state={
      manageDate:{},
      account:{}
    }
  }

  render(){
    return(
        <Layout>
          <div className="man_content_box">
             <div className="examine_box">
                <h3>待审核</h3>
                <p>
                  <span>视频:<b>{this.state.manageDate.count_tweet}</b>条</span>
                  <span>竞赛:<b>{this.state.manageDate.count_activity}</b>场</span>
                  <span>模板:<b>{this.state.manageDate.count_template}</b>套</span>
                  <span>推广:<b>3,213</b>组</span>
                  <span>贴纸:<b>{this.state.manageDate.count_filter}</b>张</span>
                  <span>认证申请:<b>{this.state.manageDate.count_verity}</b>个</span>
                </p>
             </div>
             <div className="user_box">
                <h3>账户信息</h3>
                <p>尊敬的<span> {this.state.account.name} </span>先生；此次是你第<b> {this.state.account.login_count} </b>次登陆</p>
                <p>管理身份：<span style={{color:"#ff0000"}}>超级管理员</span></p>
                <p>上次登陆时间：<span>{this.state.account.last_time}</span></p>
                <p>上次登陆IP：<span>{this.state.account.last_ip}</span></p>
             </div>
          </div>

           <div className="man_dispose">
              <h3>待处理</h3>
              <span>投诉与反馈：未处理<b>{this.state.manageDate.count_complain}</b>条</span>
              <span>提现申请：未处理信息<b>{this.state.manageDate.count_withdraw} </b>条</span>
           </div>
           <div className="lately_operation">
              <h3>最近操作记录</h3>
              <ul>
                <li>
                  <span>推荐视频：<b>春风十里你</b></span>
                  <span className="man_time">2017-08-08 16:20</span>
                </li>
                <li>
                  <span>推荐视频：<b>春风十里你</b></span>
                  <span className="man_time">2017-08-08 16:20</span>
                </li>
                <li>
                  <span>推荐视频：<b>春风十里你</b></span>
                  <span className="man_time">2017-08-08 16:20</span>
                </li>
                <li>
                  <span>推荐视频：<b>春风十里你</b></span>
                  <span className="man_time">2017-08-08 16:20</span>
                </li>
                <li>
                  <span>推荐视频：<b>春风十里你</b></span>
                  <span className="man_time">2017-08-08 16:20</span>
                </li>
                <li>
                  <span>推荐视频：<b>春风十里你</b></span>
                  <span className="man_time">2017-08-08 16:20</span>
                </li>
                <li>
                  <span>推荐视频：<b>春风十里你</b></span>
                  <span className="man_time">2017-08-08 16:20</span>
                </li>
              </ul>
              <button className="man_more" onClick={this.getMore}>更多...</button>
           </div>
        </Layout>
    )
  }
  componentDidMount(){
    this.Fetch({
      uri:'/api/admins/manage',
      callback:(res)=>{
        // console.log(res,'mange');
        this.setState({
          manageDate:res,
          account:res.admin
        })
        localStorage.setItem('username',res.admin.name)
      }
    })

  }

}

export default Manage
