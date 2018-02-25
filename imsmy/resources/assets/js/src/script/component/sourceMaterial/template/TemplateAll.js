import React, { Component } from 'react'

import CommonMenu from '../../common/table/dropdownMenu/CommonMenu'
import SelectAndTable from '../../common/table/SelectAndTable'
import TableLinkDetail from '../../common/table/TableLinkDetail'
import ResolveObj from 'utils/ResolveObj'
import Fetch from 'utils/fetch'

//模板 - 全部
class TemplateAll extends Component{
  constructor(props){
    super(props)
    this.state={
      sumnum:'',
      todaynew:'',
      menuName:{}, //批量操作按钮
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
      const templateAll = [
        { title: '分类', dataIndex: 'type', key: 'type', width:"6%",
          render:(text,record,index) =>(<div className="obj_array_box_all">
            {ResolveObj.resolveObjEvent(record.type).map((item,index)=>{
              return(
                <span>{index===0? item : `、${item}`}</span>
              )
            })}

          </div>)
        },
        { title: '来自', dataIndex: 'issuer', key:'issuer', width:"8%",
          render:(text,record) =>(<b>{record.issuer}</b>)
        },
        { title: '封面', dataIndex: 'cover',key: 'cover',  width:"10.7%",
          render:(text,record) =>(
            <TableLinkDetail
              linkUri = {`/templateAll/detail/${record.id}`}
              imgUri = {`${record.cover}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}
            />
          )
        },
        { title: '描述', dataIndex: 'description', key: 'description', width:"18.9%",
          // render:(text,record) => ("Super+吴亦凡音乐年 2017：走进吴制作人的音乐领地")
        },
        { title: '时长', dataIndex: 'duration', key:'duration', width:"5.5%",
          render:(text,record,index) =>(<b>{record.duration}</b>)
        },
        { title:  "上传日期", dataIndex: "time" , key: "time",width:"14%",
          // render:(text,record) =>( "2017-09-20 11:16:28")
        },
        { title: "操作员", dataIndex: "operator" , key: "operator" , width:"8.3%",
          render:(text,record) =>( <b>{record.operator}</b>)
        },
        { title: "下载量", dataIndex: 'count', key: 'count', width:"8.5%",
          // render:(text,record) =>{record.browse_times}
        },
        { title: "费用", dataIndex: 'intergral', key: 'intergral',width:"6%",
          // render:(text,record) =>("免费")
        },
        { title: '操作', dataIndex: 'behavior', key: 'behavior',
          render:(text,record,index) =>(
            <CommonMenu record={record.id} operation menuName={record.behavior}
              RefreshTableState={this.handleChangeRefreshTableState}
              ModalPopUri="/api/admins/fodder/fragment/changetype"
              ModalTopUri="/api/admins/fodder/fragment/changeishot"
            />)
        }
      ]
    return(
      <div>
        <SelectAndTable columns={templateAll}
            title={<h3>
              <span>总共：<b>{this.state.sumnum} </b>条视频</span>
              <span>今日新增：<b>{this.state.todaynew}</b>条</span>
            </h3>}
            selectUri="/api/admins/fodder/fragment/index"
            type="/api/admins/fodder/fragment/gettype"
            operator='/api/admins/fodder/fragment/getoperator'
            time="/api/admins/fodder/fragment/gettime"
            duration="/api/admins/fodder/fragment/getduration"
            count='/api/admins/fodder/fragment/getcount'
            uri="/api/admins/fodder/fragment/index"
            onChange={this.handleBatchMenu}
            dropdownMenu={<CommonMenu SelectAll={this.state.BatchNumber}
              // menuName={this.state.menuName}
              ModalPopUri="/api/admins/fodder/fragment/changetype"
              ModalTopUri="/api/admins/fodder/fragment/changeishot"
          />}
          RefreshTableState={this.state.refreshTable}
        />
      </div>
    )
  }
  componentDidMount(){
    Fetch.post({
      uri:'/api/admins/fodder/fragment/index',
      callback:(res)=>{
        this.setState({
          sumnum:res.sumnum,
          todaynew:res.todaynew,
        })
      },
      formData:this.handleFormData()
    })
  }

  }


export default TemplateAll
