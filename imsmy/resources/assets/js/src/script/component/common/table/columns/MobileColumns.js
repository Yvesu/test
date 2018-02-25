
// 移动端的各种表格标题

import React from 'react'
import { Link } from 'react-router'
import { Table,Icon, Button } from 'antd'
import CommonMenu from '../dropdownMenu/CommonMenu'

//移动端 - 滤镜 -全部

export const filterAll = [
  { title: '分类', dataIndex: 'type', key: 'type', width:"7.3%",
    // render:(text,record) =>("电影")
  },
  { title: '封面', dataIndex: 'covert',key: 'cover',  width:"12.3%",
    render:(text,record) =>(
      <Link to={`/filterAll/detail/${record.id}`}>
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
  { title: '名称', dataIndex: 'name', key: 'name', width:"9%",
    // render:(text,record) => ("Super+吴亦凡音乐年 2017：走进吴制作人的音乐领地")
  },
  { title: '时长', dataIndex: 'content', key:'content', width:"14%",
    render:(text,record) =>(
      <div className="cover_box">
        <img className="detail_img_hover"
          src={`${record.cover}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}/>
          <div className="detail_img_mask">
            <Icon type="play-circle"  style={{fontSize:"18px",color:"#fff"}}/>
          </div>
      </div>)
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
  { title: '操作', dataIndex: '操作', key: 'more',
    render:(text,record,index) =>(
      <CommonMenu record={record.id}  operation/>)
  }
]
