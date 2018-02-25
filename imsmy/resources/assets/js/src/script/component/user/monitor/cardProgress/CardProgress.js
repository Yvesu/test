import React, { Component } from 'react'
import { Card, Progress  } from 'antd'
import Fetch from 'utils/fetch'

class CardProgress extends Component{
  constructor(props){
    super(props)
    this.state={
      createUser:'',
      createUserP:'',
      organUser:'',
      organUserP:'',
      vipUser:'',
      vipUserP:'',
      verifyUser:'',
      verifyUserP:'',
      users:'',
      usersP:'',
      thirdUser:'',
      thirdUserP:'',
    }
  }
  render(){
    return(
      <div className='monitor_progress_box'>
        <Card title="用户占比" className="card_monitor_progress_box">
          <p>
            <Progress type="circle" percent={this.state.createUserP.replace('%','')} width={100} format={percent =>(
              <div className="progress_center_box">
                <p>创作者</p> <p>{`${percent}%`}</p>
              </div>)} />

             <p><em> {`${this.state.createUser} 位`}</em></p>
          </p>
          <p>
            <Progress type="circle" percent={this.state.organUserP.replace('%','')} width={100} format={percent =>(
              <div className="progress_center_box">
                 <p>机构</p> <p>{`${percent}%`}</p>
              </div>)} />
             <p><em> {`${this.state.organUser} 位`}</em></p>
          </p>
          <p>
            <Progress type="circle" percent={this.state.vipUserP.replace('%','')} width={100} format={percent =>(
              <div className="progress_center_box">
                 <p>VIP</p> <p>{`${percent}%`}</p>
              </div>)} />
             <p><em> {`${this.state.vipUser} 位`}</em></p>
          </p>
          <p>
            <Progress type="circle" percent={this.state.verifyUserP.replace('%','')} width={100} format={percent =>(
              <div className="progress_center_box">
                 <p>认证用户</p> <p>{`${percent}%`}</p>
              </div>)} />
             <p><em> {`${this.state.verifyUser} 位`}</em></p>
          </p>
          <p>
            <Progress type="circle" percent={this.state.usersP.replace('%','')} width={100} format={percent =>(
              <div className="progress_center_box">
                 <p>普通用户</p> <p>{`${percent}%`}</p>
              </div>)} />
             <p><em> {`${this.state.users} 位`}</em></p>
          </p>
          <p>
            <Progress type="circle" percent={this.state.thirdUserP.replace('%','')} width={100} format={percent =>(
              <div className="progress_center_box">
                 <p>第三方</p> <p>{`${percent}%`}</p>
              </div>)} />
             <p><em> {`${this.state.thirdUser} 位`}</em></p>
          </p>
        </Card>
      </div>
    )
  }
  componentDidMount(){
    Fetch.post({
      uri:'/api/admins/user/supervisory/index',
      callback:(res)=>{
        // console.log(res,'jiankong ');
        this.setState({
          createUser:res.createUserNum,
          createUserP:res.createUserNumProportion,
          organUser:res.organizationNum,
          organUserP:res.organizationNumProportion,
          vipUser:res.vipNum,
          vipUserP:res.vipNumProportion,
          verifyUser:res.verifyNum,
          verifyUserP:res.verifyNumProportion,
          users:res.generalUserNum,
          usersP:res.generalUserNumProportion,
          thirdUser:res.thirdUserNum,
          thirdUserP:res.thirdUserNumProportion,
        })
      }
    })
  }
}

export default CardProgress
