import React, { Component } from 'react'

import { filterAll } from '../../common/table/columns/MobileColumns'
import SelectAndTable from '../../common/table/SelectAndTable'
import CommonMenu from '../../common/table/dropdownMenu/CommonMenu'
import TableLinkDetail from '../../common/table/TableLinkDetail'
import ResolveObj from 'utils/ResolveObj'
import Fetch from 'utils/fetch'

//移动端 - 滤镜 - 全部
class FilterAll extends Component {
  constructor(props){
    super(props)
      this.state={
        sum:'',
        todaynew:'',
        BatchNumber:[],
        refreshTable:false,
      }
    }
    handleFormData=()=>{
      let formData= new FormData()
      // formData.append('active',0)
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
  const filterAll = [
      { title: '分类', dataIndex: 'type', key: 'type', width:"7.3%",
        render:(text,record,index) =>(<div className="obj_array_box_all">
          {ResolveObj.resolveObjEvent(record.type).map((item,index)=>{
            return(
              <span >{index===0? item : `、${item}`}</span>
            )
          })}
        </div>)
      },
      { title: '封面', dataIndex: 'cover',key: 'cover',  width:"12.3%",
        render:(text,record) =>(
          <TableLinkDetail
            linkUri={`/filterAll/detail/${record.id}`}
            imgUri={`${record.cover}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}
          />
        )
      },
      { title: '名称', dataIndex: 'name', key: 'name', width:"9%",
        render:(text,record) => (<div>{record.name}</div>)
      },
      { title: '纹理', dataIndex: 'content', key:'content', width:"14%",
        render:(text,record) =>
        <div style={{textAlign:'center'}}>
          { record.content !== null?
            <TableLinkDetail
            // linkUri={`/filterAll/detail/${record.id}`}
            imgUri={`${record.content}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}
          /> : <b>无</b> }
        </div>
      },
      { title:  "上传日期", dataIndex: "time_add" , key: "time_add",width:"14%",
        // render:(text,record) =>( "2017-09-20 11:16:28")
      },
      { title: "操作员", dataIndex: "operator" , key: "operator" , width:"10%",
        render:(text,record) =>( <b>{record.operator}</b>)
      },
      { title: "下载量", dataIndex: 'count', key: 'count', width:"10%",
        // render:(text,record) =>{record.browse_times}
      },
      { title: "费用", dataIndex: 'intrgral', key: 'intrgral',width:"10%",
        render:(text,record) =>(<b>{record.intrgral}</b>)
      },
      { title: '操作', dataIndex: 'behavior', key: 'behavior',
        render:(text,record,index) =>(
          <CommonMenu record={record.id}  operation
            menuName={record.behavior}
          />)
      }
    ]
    return(
      <div className="filter_all_need_modify">
        <SelectAndTable
          columns={filterAll}
          title={<h3>
            <span>总共：<b>{this.state.sum} </b></span>
            <span>今日新增：<b>{this.state.todaynew}</b></span>
          </h3>}
          // type="/api/admins/mobile/filter/addfiltertype"
          uri="/api/admins/mobile/filter/index"
          onChange={this.handleBatchMenu}
          dropdownMenu={<CommonMenu SelectAll={this.state.BatchNumber}

          />}
          RefreshTableState={this.state.refreshTable}

        />
        {/* <SelectMoblie uri='/api/admins/mobile/filter/index' /> */}
        {/* <TableContainer columns={filterAll} uri='/api/admins/mobile/filter/index'
        // formData={this.handleFormData()}
        onChange={this.handleBatchMenu}
        DropdownMenu={<CommonMenu SelectAll={this.state.BatchNumber}/>}
        /> */}
      </div>
    )
  }
  componentDidMount(){
    Fetch.post({
      uri:"/api/admins/mobile/filter/index",
      callback:(res)=>{
        console.log(res,'lvjing');
        this.setState({
          sum:res.sum,
          todaynew:res.todaynew
        })
      }
    })
  }
}

export default FilterAll
