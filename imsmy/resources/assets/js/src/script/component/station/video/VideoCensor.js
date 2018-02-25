import React, { Component } from 'react'

//视频审查
import CensorMenu from '../common/table/dropdownMenu/CensorMenu'
import SelectAndTable from '../../common/table/SelectAndTable'
import TableLinkDetail from '../../common/table/TableLinkDetail'
import CommonMenu from '../../common/table/dropdownMenu/CommonMenu'
import ResolveObj from 'utils/ResolveObj'
import TimeChange from 'utils/format'
import Fetch from 'utils/fetch'

class VideoCensor extends Component {
  constructor(props){
    super(props)
    this.state={
      count:'',
      todayCount:'',
      BatchNumber:[],
      refreshTable:false,
    }
  }
  handleFormData=()=>{
    let formData= new FormData()
    formData.append('active',0)
    return formData
  }
  handleBatchMenu=(value)=>{
    // console.log(value,'hahh');
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
    const videoCensor = [
      { title: '评分', dataIndex: '评分', key: 'id', width:"7%",
        render:(text,record) =>(<b>8.0分</b>)
      },
      { title: '类型', dataIndex: 'type', key:'type', width:"9%",
        render:(text,record) =>(<b>{record.type}</b>)
      },
      { title: '封面', dataIndex: 'screen_shot',key: 'screen_shot',  width:"10.7%",
        render:(text,record) =>(
          <TableLinkDetail
            linkUri={`/videoCensor/detail/${record.id}`}
            imgUri={`${record.screen_shot}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}
          />
        )
      },
      { title: '描述', dataIndex: '描述', key: '描述', width:"16.4%",
        render:(text,record) => ("对视频的描述")
      },
      { title: '时长', dataIndex: 'duration', key:'duration', width:"7%",
        render:(text,record,index) =>(<b>{TimeChange.formatSec(record.duration)}</b>)
      },
      { title:  "上传日期", dataIndex: "上传日期" , key: "time",width:"14%",
        render:(text,record) =>( "2017-09-20 11:16:28")
      },
      { title: "来自", dataIndex:  "来自" , key: "来自" ,width:"11%",  width:"14%",
        render:(text,record) =>( <b>2017快男全国晋级赛</b>)
      },
      { title: "播放", dataIndex: 'bofang', key: 'bofang', width:"14%",
        render:(text,record) =>{record.browse_times}
      },
      { title: '操作', dataIndex: '操作', key: 'more',
        render:(text,record,index) =>(
          <CensorMenu record={record.id} operation/>)
      }
    ]

    return(
      <div>
        <SelectAndTable
          title={<h3>
            <span>总共：<b>{this.state.count} </b>条视频</span>
            <span>今日新增：<b>{this.state.todayCount}</b>条</span>
          </h3>}
          searchPlaceholder='请搜索关键字'
          uri="/api/admins/video/index"
          columns={videoCensor}
          onChange={this.handleBatchMenu}
          formData={this.handleFormData()}
          dropdownMenu={<CommonMenu SelectAll={this.state.BatchNumber}
            ModalPopUri="/api/admins/fodder/fragment/changetype"
            ModalTopUri="/api/admins/fodder/fragment/changeishot"
        />}
          RefreshTableState={this.state.refreshTable}
        />
      </div>
    )
  }
  componentDidMount(){
    let formData = new FormData()
    formData.append('active',0)
    Fetch.post({
      uri:"/api/admins/video/index",
      callback:(res)=>{
        this.setState({
          count:res.count,
          todayCount:res.today_count
        })
      },
      formData:formData
    })
  }
}

export default VideoCensor
