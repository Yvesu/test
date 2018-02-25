import React, { Component } from 'react'

import SelectAndTable from '../../common/table/SelectAndTable'
import CommonMenu from '../../common/table/dropdownMenu/CommonMenu'
import BatchCommonMenu from '../../common/table/dropdownMenu/BatchCommonMenu'
import TableAvatar from '../../common/table/TableAvatar'
import Fetch from 'utils/fetch'
//用户管理页 - 第三方

class ManageThird extends Component {
  constructor(props){
    super(props)
    this.state={
      thirdUser:'',
      weixin:'',
      weibo:'',
      qq:'',
      todayAdd:'',
      todayWeiXin:'',
      todayWeiBo:'',
      todayQQ:'',
      BatchNumber:[],
      BatchName:{},
      refreshTable:false,
    }
  }
  handleBatchMenu=(value)=>{
    this.setState({
      BatchNumber:value
    })
  }

  handleChangeRefreshTableState=(value)=>{
    this.setState({
      refreshTable:value
    })
  }

  render(){
    const userManageThird = [
      { title: 'ID', dataIndex: 'id', key: 'id', width:"6%",
      },
      { title: '第三方', dataIndex: 'third', key:'third', width:"8%",
        render:(text,record) =>(<b>{record.third}</b>)
      },
      { title: '头像', dataIndex: 'avatar',key: 'avatar',  width:"8%",
        render:(text,record) =>(
          <TableAvatar imgUri={record.avatar}/>
        )
      },
      { title: '昵称', dataIndex: 'nickname', key: 'nickname', width:"10%",
        render:(text,record) => (<div>{
          record.sex===0? (<b style={{color:'#ff00a6'}}>{record.nickname}</b>) :
              (<b>{record.nickname}</b>) }</div>)
      },
      { title: '手机', dataIndex: 'phone', key:'phone', width:"10%",
        // render:(text,record,index) =>(<b>{record.phone}</b>)
      },
      { title:  "注册日期", dataIndex: "time_add" , key: "time_add",width:"14%",
        render:(text,record) =>(<b>{record.time_add.date.replace('.000000','')}</b>)
      },
      { title: "活跃指数", dataIndex: "activeIndex" , key: "activeIndex" , width:"10%",
        render:(text,record) =>( <b>{record.activeIndex}</b>)
      },
      { title: "作品", dataIndex: 'work_count', key: 'work_count', width:"7%",
        // render:(text,record) =>{record.browse_times}
      },
      { title: "播放量", dataIndex: 'play_count', key: 'play_count',width:"7%",
        // render:(text,record) =>("免费")
      },
      { title: "资产", dataIndex: 'integralSum', key: 'integralSum',width:"7%",
        // render:(text,record) =>("免费")
      },
      { title: '操作', dataIndex: 'behavior', key: 'behavior',width:'8%',
        render:(text,record,index) =>(
          <CommonMenu record={record.id} operation menuName={record.behavior}
            RefreshTableState={this.handleChangeRefreshTableState}
            activeUri="/api/admins/user/manage/common/dochoiceness"
            cancelUri="/api/admins/user/manage/common/cancelchoiceness"
            goupUri='/api/admins/user/manage/common/levelup'
            frozenUri='/api/admins/user/manage/common/dostop'
          />)
      }
    ]
    return(
      <div className="user_manage_third_box">
        <SelectAndTable columns={userManageThird}
          title={
            <h3>
              <span>第三方登录用户：<b>{this.state.thirdUser}</b></span>
              <span>微信：<b>{this.state.weixin}</b></span>
              <span>微博：<b>{this.state.weibo}</b></span>
              <span>QQ：<b>{this.state.qq}</b></span>
              <span style={{maring:"0 10"}}>|</span>
              <span>今日新增：<b>{this.state.todayAdd}</b></span>
              <span>微信：<b>{this.state.todayWeiXin}</b></span>
              <span>微博：<b>{this.state.todayWeiBo}</b></span>
              <span>QQ：<b>{this.state.todayQQ}</b></span>
            </h3> }
            uri='/api/admins/user/manage/thirdparty'
            // user='/api/admins/user/manage/index/getVipLevel'
            third='/api/admins/user/manage/thirdparty/thirdtype'
            // userVip='/api/admins/user/manage/vipuser/viplevel'
            // auditor='/api/admins/user/manage/common/checker'
            fans='/api/admins/user/manage/common/getfansnum'
            playCount='/api/admins/user/manage/common/getplaycount'
            works='/api/admins/user/manage/common/productionnum'
            assets='/api/admins/user/manage/common/integralnum'

            onChange={this.handleBatchMenu}
            dropdownMenu={<BatchCommonMenu SelectAll={this.state.BatchNumber}
              menuName={this.state.BatchName}
              RefreshTableState={this.handleChangeRefreshTableState}
              activeUri="/api/admins/user/manage/common/dochoiceness"
              cancelUri="/api/admins/user/manage/common/cancelchoiceness"
              goupUri='/api/admins/user/manage/common/levelup'
              frozenUri="/api/admins/user/manage/common/dostop"
          />}
          RefreshTableState={this.state.refreshTable}
          searchPlaceholder='昵称／ID'
        />
      </div>
    )
  }
  componentDidMount(){
    Fetch.post({
      uri:'/api/admins/user/manage/thirdparty',
      callback:(res)=>{
        // console.log(res,'disanf');
        this.setState({
          thirdUser:res.thirdpartyUserNum,
          weixin:res.thirdpartyWeixinProportion,
          weibo:res.thirdpartyWeiboProportion,
          qq:res.thirdpartyQQProportion,
          todayAdd:res.todayNewUserNum,
          todayWeiXin:res.todayNewWeixinProportion,
          todayWeiBo:res.todayNewWeiboProportion,
          todayQQ:res.todayNewQQProportion,
          BatchName:res.batchBehavior
        })
      }
    })
  }
}

export default ManageThird
