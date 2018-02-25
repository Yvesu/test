import React, { Component } from 'react'
import SelectAndTable from '../../common/table/SelectAndTable'
import CommonMenu from '../../common/table/dropdownMenu/CommonMenu'
import BatchCommonMenu from '../../common/table/dropdownMenu/BatchCommonMenu'
import TableAvatar from '../../common/table/TableAvatar'
import Fetch from 'utils/fetch'
//用户管理页 - 审查者

class ManageChoice extends Component {
  constructor(props){
    super(props)
    this.state={
      choiceUser:'',
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
    const userManageChoice = [
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
      { title:  "注册日期", dataIndex: "created_time" , key: "created_time",width:"14%",
        render:(text,record) =>(<b>{record.created_time.date.replace('.000000','')}</b>)
      },
      { title: "活跃指数", dataIndex: "activeIndex" , key: "activeIndex" , width:"14%",
          render:(text,record) =>(<b>{record.activeIndex}</b>)
      },
      { title: "推选人", dataIndex: 'checker', key: 'checker', width:"7%",
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
      <div className="user_manage_choice_box">
        <SelectAndTable columns={userManageChoice}
          title={
            <h3>
              <span>精选用户：<b>{this.state.choiceUser}</b></span>
              <span>女：<b>{this.state.girl}</b></span>
              <span>男：<b>{this.state.boy}</b></span>
              <span style={{maring:"0 10"}}>|</span>
              <span>今日新增：<b>{this.state.todayAdd}</b></span>
              <span>女：<b>{this.state.todyGirl}</b></span>
              <span>男：<b>{this.state.todyBoy}</b></span>
            </h3> }
            uri='/api/admins/user/manage/choiceness'
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
      uri:'/api/admins/user/manage/choiceness',
      callback:(res)=>{
        console.log(res,'jingxuan');
        this.setState({
          choiceUser:res.choicenessNum,
          girl:res.womenChoicenessProportion,
          boy:res.manChoicenessProportion,
          todayAdd:res.todayNewChoicenessNum,
          todyGirl:res.todayNewChoicenessNum,
          todyBoy:res.todayNewChoicenessManNum,
          BatchName:res.batchBehavior
        })
      }
    })
  }
}

export default ManageChoice
