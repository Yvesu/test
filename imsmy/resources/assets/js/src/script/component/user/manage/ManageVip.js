import React, { Component } from 'react'

import SelectAndTable from '../../common/table/SelectAndTable'
import CommonMenu from '../../common/table/dropdownMenu/CommonMenu'
import BatchCommonMenu from '../../common/table/dropdownMenu/BatchCommonMenu'
import TableAvatar from '../../common/table/TableAvatar'
import Fetch from 'utils/fetch'
//用户管理页 - vip

class ManageVip extends Component {
  constructor(props){
    super(props)
    this.state={
      vipUser:'',
      girl:'',
      boy:'',
      todayAdd:'',
      todyGirl:'',
      todyBoy:'',
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
    const userManageVip = [
      { title: 'ID', dataIndex: 'id', key: 'id', width:"6%",
      },
      { title: '等级', dataIndex: 'vipLevel', key:'vipLevel', width:"8%",
        render:(text,record) =>(<b>{record.vipLevel}</b>)
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
      { title: "到期日期", dataIndex: "activeIndextime" , key: "activeIndextime" , width:"17%",
        render:(text,record) =>( <b>{record.activeIndex}</b>)
      },
      { title: "类型", dataIndex: 'activeIndex', key: 'activeIndex', width:"7%",
        render:(text,record) =>(<b>{record.activeIndex}</b>)
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
      <div className="user_manage_vip_box">
        <SelectAndTable columns={userManageVip}
          title={
            <h3>
              <span>VIP：<b>{this.state.vipUser}</b></span>
              <span>女：<b>{this.state.girl}</b></span>
              <span>男：<b>{this.state.boy}</b></span>
              <span style={{maring:"0 10"}}>|</span>
              <span>今日新增：<b>{this.state.todayAdd}</b></span>
              <span>女：<b>{this.state.todyGirl}</b></span>
              <span>男：<b>{this.state.todyBoy}</b></span>
            </h3> }
            uri='/api/admins/user/manage/vipuser'
            // user='/api/admins/user/manage/index/getVipLevel'
            // third='/api/admins/user/manage/thirdparty/thirdtype'
            userVip='/api/admins/user/manage/vipuser/viplevel'
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
      uri:'/api/admins/user/manage/vipuser',
      callback:(res)=>{
        // console.log(res,'vip');
        this.setState({
          vipUser:res.vipUserNum,
          girl:res.vipWomenUserProportino,
          boy:res.vipManUserProportino,
          todayAdd:res.todayNewVipUserNum,
          todyGirl:res.todayWomen,
          todyBoy:res.todayMan,
          BatchName:res.batchBehavior
        })
      }
    })
  }
}

export default ManageVip
