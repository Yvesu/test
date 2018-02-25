import React, { Component } from 'react'
import SelectAndTable from '../../common/table/SelectAndTable'
import CommonMenu from '../../common/table/dropdownMenu/CommonMenu'
import BatchCommonMenu from '../../common/table/dropdownMenu/BatchCommonMenu'
import TableAvatar from '../../common/table/TableAvatar'
import Fetch from 'utils/fetch'
//用户管理页 - 认证用户

class ManageVerify extends Component {
  constructor(props){
    super(props)
    this.state={
      verifyUser:'',
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
    const userManageVerify = [
      { title: 'ID', dataIndex: 'id', key: 'id', width:"6%",
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
      { title: '联系方式', dataIndex: 'phone', key:'phone', width:"10%",
        // render:(text,record,index) =>(<b>{record.phone}</b>)
      },
      { title: '介绍', dataIndex: 'des', key:'des', width:"18%",
        render:(text,record,index) =>(<b>{record.des}</b>)
      },
      { title:  "注册日期", dataIndex: "time_add" , key: "time_add",width:"14%",
        render:(text,record) =>(<b>{record.time_add.date.replace('.000000','')}</b>)
      },
      { title: "认证日期", dataIndex: "verify_time" , key: "verify_time" , width:"14%",
          render:(text,record) =>(<b>{record.verify_time}</b>)
      },
      { title: "审核人", dataIndex: 'checker', key: 'checker', width:"7%",
        // render:(text,record) =>(<b>{record.work_count}</b>)
      },
      { title: '操作', dataIndex: 'behavior', key: 'behavior',
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
      <div className="user_manage_verify_box">
        <SelectAndTable columns={userManageVerify}
          title={
            <h3>
              <span>认证用户：<b>{this.state.verifyUser}</b></span>
              <span>女：<b>{this.state.girl}</b></span>
              <span>男：<b>{this.state.boy}</b></span>
              <span style={{maring:"0 10"}}>|</span>
              <span>今日新增：<b>{this.state.todayAdd}</b></span>
              <span>女：<b>{this.state.todyGirl}</b></span>
              <span>男：<b>{this.state.todyBoy}</b></span>
            </h3> }
            uri='/api/admins/user/manage/verifyuser'
            auditor='/api/admins/user/manage/common/checker'
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
      uri:'/api/admins/user/manage/verifyuser',
      callback:(res)=>{
        // console.log(res,'认证');
        this.setState({
          verifyUser:res.verifyUserNum,
          girl:res.verifyWomenProportion,
          boy:res.verifyManProportion,
          todayAdd:res.todayNewVerifyNum,
          todyGirl:res.todayNewVerifyWomenNum,
          todyBoy:res.todayNewOrganizationManNum,
          BatchName:res.batchBehavior
        })
      }
    })
  }
}


export default ManageVerify
