
//素材中的表格标题

import React from 'react'
import { Link } from 'react-router'
import { Table,Icon, Button } from 'antd'
import CommonMenu from '../../../../common/table/dropdownMenu/CommonMenu'
import SortNormalMenu from '../dropdownMenu/SortNormalMenu'
import SortDisableMenu from '../dropdownMenu/SortDisableMenu'

// import TimeChange from 'utils/format'
function resolveObjEvent(data){
  var obj = data
  var props = []
  for(var i in obj){
      props.push(obj[i])
  }
  return props
}

//模板 - 全部
export const templateAll = [
  { title: '分类', dataIndex: 'type', key: 'type', width:"6%",
    render:(text,record,index) =>(<div className="obj_array_box_all">
      {resolveObjEvent(record.type).map((item,index)=>{
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
      <Link to={`/TemplateAll/detail/${record.id}`}>
        <div className="cover_box">
          <img className="detail_img_hover"
            src={`${record.cover}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}/>
            <div className="detail_img_mask">
              <Icon type="play-circle"  style={{fontSize:"18px",color:"#fff"}}/>
            </div>
        </div>
      </Link>
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
      <CommonMenu record={record.id} operation menuName={record.behavior}/>)
  }
]

//模板 - 分类 - 正常
export const sortNormal = [
  { title: '图标', dataIndex: '图标', key: 'fenlei', width:"10.7%",
    render:(text,record) =>(
      <div className="sort_normal_icon">

      </div>
    )
  },
  { title: '名称', dataIndex: '名称', key:'来自', width:"11%",
    render:(text,record) =>(<b>KrisWu</b>)
  },

  { title: '创建日期', dataIndex: '创建日期', key: '创建日期', width:"18%",
    render:(text,record) => ("2017-09-20 11:16:28")
  },
  { title: '创建人', dataIndex: '创建人', key:'创建人', width:"11%",
    render:(text,record,index) =>(<b>华哥</b>)
  },
  { title:  "数量", dataIndex: "数量" , key: "time",width:"10%",
    render:(text,record) =>( "3000")
  },
  { title: "下载量", dataIndex: "" , key: "下载量" , width:"11%",
    render:(text,record) =>( <b>2000</b>)
  },
  { title: "浏览量", dataIndex: '浏览量', key: '浏览量', width:"8.5%",
    render:(text,record) =>("21312322")
  },
  { title: "占用/占比", dataIndex: '占用/占比', key: '占用/占比',width:"10%",
    render:(text,record) =>(<b>1232.22 GB／32%</b>)
  },
  { title: '操作', dataIndex: '操作', key: 'more',
    render:(text,record,index) =>(
      <SortNormalMenu record={record.id} operation menuName={record.behavior}/>)
  }
]

//模板 - 分类 - 停用
export const sortDisable = [
  { title: '图标', dataIndex: '图标', key: '图标', width:"10.7%",
    render:(text,record) =>(
      <div className="sort_normal_icon"></div>
    )
  },
  { title: '名称', dataIndex: '名称', key:'名称', width:"11%",
    render:(text,record) =>("音乐")
  },

  { title: '停用日期', dataIndex: '停用日期', key: '停用日期', width:"18%",
    render:(text,record) => ("2017-09-20 11:16:28")
  },
  { title: '理由', dataIndex: '理由', key:'理由', width:"11%",
    render:(text,record,index) =>("测试")
  },
  { title:  "操作员", dataIndex: "操作员" , key: "time",width:"40%",
    render:(text,record) =>( <b>GOOBIRD</b>)
  },
  { title: '操作', dataIndex: '操作', key: 'more',width:"10%",
    render:(text,record,index) =>(
      <SortDisableMenu record={record.id} operation menuName={record.behavior}/>)
  }
]

//片段 - 全部

export const fragmentAll = [
  { title: '分类', dataIndex: 'type', key: 'type', width:"6%",
    render:(text,record,index) =>(<div className="obj_array_box_all">
      {resolveObjEvent(record.type).map((item,index)=>{
        return(
          <span >{index===0? item : `、${item}`}</span>
        )
      })}
    </div>)
  },
  { title: '来自', dataIndex: 'issuer', issuer:'来自', width:"8%",
    render:(text,record) =>(<b>{record.issuer}</b>)
  },
  { title: '封面', dataIndex: 'cover',key: 'cover',  width:"10.7%",
    render:(text,record) =>(
      <Link to={`/TemplateAll/detail/${record.id}`}>
        <div className="cover_box">
          <img className="detail_img_hover"
            src={`${record.cover}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}/>
            <div className="detail_img_mask">
              <Icon type="play-circle"  style={{fontSize:"18px",color:"#fff"}}/>
            </div>
        </div>
      </Link>
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
    render:(text,record,index) =>( <b>{record.operator}</b>)
  },
  { title: "下载量", dataIndex: 'count', key: 'count', width:"8.5%",
    // render:(text,record) =>{record.browse_times}
  },
  { title: "费用", dataIndex: 'intergral', key: 'intergral',width:"6%",
    // render:(text,record) =>("免费")
  },
  { title: '操作', dataIndex: 'behavior', key: 'behavior',
    render:(text,record,index) =>(
      <CommonMenu record={record.id}  operation
        menuName={record.behavior}

        ModalPopUri="/api/admins/fodder/fragment/changetype"
        ModalTopUri="/api/admins/fodder/fragment/changeishot"
      />)
  }
]

//片段 - 分类 - 正常
export const sortNormalFragment = [
  { title: '图标', dataIndex: '图标', key: 'fenlei', width:"10.7%",
    render:(text,record) =>(
      <div className="sort_normal_icon">

      </div>
    )
  },
  { title: '名称', dataIndex: 'name', key:'name', width:"11%",
    render:(text,record) =>(<b>{record.name}</b>)
  },

  { title: '创建日期', dataIndex: 'time_add', key: 'time_add', width:"18%",
    // render:(text,record) => ("2017-09-20 11:16:28")
  },
  { title: '创建人', dataIndex: 'operator', key:'operator', width:"11%",
    render:(text,record,index) =>(<b>{record.operator}</b>)
  },
  { title:  "数量", dataIndex: "num" , key: "num",width:"10%",
    // render:(text,record) =>( "3000")
  },
  { title: "下载量", dataIndex: "downloadnum" , key: "downloadnum" , width:"11%",
    render:(text,record) =>( <b>{record.downloadnum}</b>)
  },
  { title: "浏览量", dataIndex: '浏览量', key: '浏览量', width:"8.5%",
    render:(text,record) =>("21312322")
  },
  { title: "占用/占比", dataIndex: '占用/占比', key: '占用/占比',width:"12%",
    render:(text,record) =>(<b>1232.22 GB／32%</b>)
  },
  { title: '操作', dataIndex: 'behavior', key: 'behavior',
    render:(text,record,index) =>(
      <CommonMenu record={record.id} operation menuName={record.behavior}
        downUri='/api/admins/fodder/fragment/down'
        upUri='/api/admins/fodder/fragment/up'
      />)
  }
]

//片段 - 分类 - 停用
export const sortDisableFragment = [
  { title: '图标', dataIndex: '图标', key: '图标', width:"10.7%",
    render:(text,record) =>(
      <div className="sort_normal_icon"></div>
    )
  },
  { title: '名称', dataIndex: '名称', key:'名称', width:"11%",
    render:(text,record) =>("音乐")
  },

  { title: '停用日期', dataIndex: '停用日期', key: '停用日期', width:"18%",
    render:(text,record) => ("2017-09-20 11:16:28")
  },
  { title: '理由', dataIndex: '理由', key:'理由', width:"11%",
    render:(text,record,index) =>("测试")
  },
  { title:  "操作员", dataIndex: "操作员" , key: "time",width:"40%",
    render:(text,record) =>( <b>GOOBIRD</b>)
  },
  { title: '操作', dataIndex: '操作', key: 'more',width:"10%",
    render:(text,record,index) =>(
      <CommonMenu record={record.id} operation menuName={record.behavior}/>)
  }
]
